<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Implementación avanzada en la base de datos (PostgreSQL):
 *
 *  - Trigger trg_actualizar_estado_vacante: cierra la vacante cuando se cubren todos sus puestos.
 *  - Trigger trg_historial_postulacion: registra cada cambio de estado de una postulación.
 *  - Procedimiento sp_reporte_empresa: reporte de una empresa en un rango de fechas.
 *
 * En PostgreSQL un trigger llama a una FUNCIÓN que devuelve "trigger", y un procedimiento
 * que devuelve filas se escribe como FUNCTION ... RETURNS TABLE.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------------------------------------------------------------
        // Trigger 1: trg_actualizar_estado_vacante
        // AFTER UPDATE OF estado ON postulaciones
        // Si una postulación pasa a 'contratado' y ya se cubrieron todos los
        // puestos de la vacante, la vacante cambia a 'cerrada' y deja de
        // mostrarse en el buscador público.
        // ---------------------------------------------------------------
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION fn_actualizar_estado_vacante()
            RETURNS trigger AS $$
            DECLARE
                v_contratados INTEGER;
                v_puestos     INTEGER;
            BEGIN
                IF NEW.estado = 'contratado' AND OLD.estado IS DISTINCT FROM NEW.estado THEN
                    SELECT COUNT(*) INTO v_contratados
                    FROM postulaciones
                    WHERE vacante_id = NEW.vacante_id
                      AND estado = 'contratado';

                    SELECT puestos INTO v_puestos
                    FROM vacantes
                    WHERE id = NEW.vacante_id;

                    IF v_contratados >= v_puestos THEN
                        UPDATE vacantes
                        SET estado = 'cerrada', updated_at = NOW()
                        WHERE id = NEW.vacante_id
                          AND estado <> 'cerrada';
                    END IF;
                END IF;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER trg_actualizar_estado_vacante
            AFTER UPDATE OF estado ON postulaciones
            FOR EACH ROW
            EXECUTE FUNCTION fn_actualizar_estado_vacante();
        SQL);

        // ---------------------------------------------------------------
        // Trigger 2: trg_historial_postulacion
        // AFTER INSERT OR UPDATE OF estado ON postulaciones
        // Guarda en historial_postulaciones el estado anterior y el nuevo.
        // ---------------------------------------------------------------
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION fn_historial_postulacion()
            RETURNS trigger AS $$
            BEGIN
                IF TG_OP = 'INSERT' THEN
                    INSERT INTO historial_postulaciones (postulacion_id, estado_anterior, estado_nuevo, cambiado_en)
                    VALUES (NEW.id, NULL, NEW.estado, NOW());
                ELSIF OLD.estado IS DISTINCT FROM NEW.estado THEN
                    INSERT INTO historial_postulaciones (postulacion_id, estado_anterior, estado_nuevo, cambiado_en)
                    VALUES (NEW.id, OLD.estado, NEW.estado, NOW());
                END IF;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER trg_historial_postulacion
            AFTER INSERT OR UPDATE OF estado ON postulaciones
            FOR EACH ROW
            EXECUTE FUNCTION fn_historial_postulacion();
        SQL);

        // ---------------------------------------------------------------
        // Procedimiento almacenado: sp_reporte_empresa
        // Uso: SELECT * FROM sp_reporte_empresa(1, '2026-01-01', '2026-12-31');
        // ---------------------------------------------------------------
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION sp_reporte_empresa(
                p_empresa_id BIGINT,
                p_desde      DATE,
                p_hasta      DATE
            )
            RETURNS TABLE (
                vacantes_publicadas    BIGINT,
                total_postulaciones    BIGINT,
                entrevistas_realizadas BIGINT,
                total_contratados      BIGINT,
                promedio_match         NUMERIC
            ) AS $$
            BEGIN
                RETURN QUERY
                SELECT
                    (SELECT COUNT(*)
                       FROM vacantes v
                      WHERE v.empresa_id = p_empresa_id
                        AND v.deleted_at IS NULL
                        AND v.publicada_en::date BETWEEN p_desde AND p_hasta),

                    (SELECT COUNT(*)
                       FROM postulaciones p
                       JOIN vacantes v ON v.id = p.vacante_id
                      WHERE v.empresa_id = p_empresa_id
                        AND p.created_at::date BETWEEN p_desde AND p_hasta),

                    (SELECT COUNT(*)
                       FROM entrevistas e
                       JOIN postulaciones p ON p.id = e.postulacion_id
                       JOIN vacantes v      ON v.id = p.vacante_id
                      WHERE v.empresa_id = p_empresa_id
                        AND e.estado = 'realizada'
                        AND e.fecha_hora::date BETWEEN p_desde AND p_hasta),

                    (SELECT COUNT(*)
                       FROM postulaciones p
                       JOIN vacantes v ON v.id = p.vacante_id
                      WHERE v.empresa_id = p_empresa_id
                        AND p.estado = 'contratado'
                        AND p.updated_at::date BETWEEN p_desde AND p_hasta),

                    (SELECT COALESCE(ROUND(AVG(p.porcentaje_match), 2), 0)
                       FROM postulaciones p
                       JOIN vacantes v ON v.id = p.vacante_id
                      WHERE v.empresa_id = p_empresa_id
                        AND p.created_at::date BETWEEN p_desde AND p_hasta);
            END;
            $$ LANGUAGE plpgsql;
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            DROP TRIGGER IF EXISTS trg_historial_postulacion ON postulaciones;
            DROP TRIGGER IF EXISTS trg_actualizar_estado_vacante ON postulaciones;
            DROP FUNCTION IF EXISTS fn_historial_postulacion();
            DROP FUNCTION IF EXISTS fn_actualizar_estado_vacante();
            DROP FUNCTION IF EXISTS sp_reporte_empresa(BIGINT, DATE, DATE);
        SQL);
    }
};
