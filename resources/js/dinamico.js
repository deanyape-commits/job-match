/**
 * Efectos dinámicos de JobMatch.
 *
 * - Animaciones: aparición al hacer scroll, contadores, círculo de match y texto rotativo.
 * - Funciones en vivo (Alpine.js): búsqueda con sugerencias, campanita que se actualiza
 *   sola, guardar vacantes y cambiar estados sin recargar, y avisos flotantes.
 *
 * Todo respeta la preferencia del sistema operativo "reducir movimiento".
 */

const reducirMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;

/** fetch con JSON y el token CSRF de Laravel. */
async function pedir(url, opciones = {}) {
    const respuesta = await fetch(url, {
        credentials: 'same-origin',
        ...opciones,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf(),
            ...(opciones.body ? { 'Content-Type': 'application/json' } : {}),
            ...(opciones.headers || {}),
        },
    });
    const datos = await respuesta.json().catch(() => ({}));
    if (!respuesta.ok) {
        throw new Error(datos.mensaje || datos.message || 'Ocurrió un error. Inténtalo de nuevo.');
    }
    return datos;
}

/* ================================================================
   1. Aparición al hacer scroll + contadores + círculo de match
================================================================ */

function animarContador(el) {
    const final = Number(el.dataset.contador);
    const formato = new Intl.NumberFormat('es-PE');
    if (reducirMovimiento || !final) {
        el.textContent = formato.format(final);
        return;
    }
    const duracion = 1400;
    const inicio = performance.now();
    const paso = (ahora) => {
        const t = Math.min(1, (ahora - inicio) / duracion);
        const suave = 1 - Math.pow(1 - t, 3); // desacelera al final
        el.textContent = formato.format(Math.round(final * suave));
        if (t < 1) requestAnimationFrame(paso);
    };
    requestAnimationFrame(paso);
}

export function iniciarAnimaciones() {
    document.documentElement.classList.add('js');

    const elementos = document.querySelectorAll('[data-revelar], [data-contador], [data-anillo]');

    const revelar = (el) => {
        if (el.dataset.revelarRetraso) {
            el.style.transitionDelay = `${el.dataset.revelarRetraso}ms`;
        }
        el.classList.add('revelado');
        if (el.dataset.contador !== undefined) {
            animarContador(el);
        }
    };

    // Los contadores empiezan en 0 (el HTML trae el número final por si no hay JS)
    document.querySelectorAll('[data-contador]').forEach((el) => {
        if (!reducirMovimiento) el.textContent = '0';
    });

    if (reducirMovimiento || !('IntersectionObserver' in window)) {
        elementos.forEach(revelar);
        return;
    }

    const observador = new IntersectionObserver((entradas) => {
        entradas.forEach((entrada) => {
            if (entrada.isIntersecting) {
                revelar(entrada.target);
                observador.unobserve(entrada.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

    elementos.forEach((el) => observador.observe(el));
}

/* ================================================================
   2. Componentes Alpine.js
================================================================ */

export function registrarComponentes(Alpine) {
    /* ---------- Avisos flotantes (toasts) ---------- */
    Alpine.store('avisos', {
        lista: [],
        siguiente: 1,
        mostrar(texto, tipo = 'exito') {
            const id = this.siguiente++;
            this.lista.push({ id, texto, tipo });
            setTimeout(() => this.cerrar(id), 5000);
        },
        cerrar(id) {
            this.lista = this.lista.filter((a) => a.id !== id);
        },
    });
    window.avisar = (texto, tipo) => Alpine.store('avisos').mostrar(texto, tipo);

    /* ---------- Texto rotativo ---------- */
    Alpine.data('rotador', (palabras = [], intervalo = 2600) => ({
        palabras,
        i: 0,
        visible: true,
        get actual() {
            return this.palabras[this.i] ?? '';
        },
        init() {
            if (reducirMovimiento || this.palabras.length < 2) return;
            setInterval(() => {
                this.visible = false;
                setTimeout(() => {
                    this.i = (this.i + 1) % this.palabras.length;
                    this.visible = true;
                }, 300);
            }, intervalo);
        },
    }));

    /* ---------- Búsqueda con sugerencias ---------- */
    Alpine.data('buscador', (url, valorInicial = '') => ({
        q: valorInicial,
        grupos: [],
        abierto: false,
        activo: -1,
        temporizador: null,
        get opciones() {
            return this.grupos.flatMap((g) => g.items);
        },
        escribir() {
            clearTimeout(this.temporizador);
            if (this.q.trim().length < 2) {
                this.grupos = [];
                this.abierto = false;
                return;
            }
            this.temporizador = setTimeout(() => this.cargar(), 250);
        },
        async cargar() {
            try {
                const datos = await pedir(`${url}?q=${encodeURIComponent(this.q.trim())}`);
                this.grupos = datos.grupos.filter((g) => g.items.length);
                this.activo = -1;
                this.abierto = this.grupos.length > 0;
            } catch {
                this.abierto = false;
            }
        },
        mover(delta) {
            if (!this.abierto) return;
            const total = this.opciones.length;
            this.activo = (this.activo + delta + total) % total;
        },
        elegir(evento) {
            const opcion = this.opciones[this.activo];
            if (this.abierto && opcion) {
                evento.preventDefault();
                window.location.href = opcion.url;
            }
        },
        indice(item) {
            return this.opciones.indexOf(item);
        },
    }));

    /* ---------- Campanita que se actualiza sola ---------- */
    Alpine.data('campanita', (url, inicial) => ({
        abierto: false,
        sinLeer: inicial.sin_leer,
        items: inicial.items,
        sacudir: false,
        init() {
            // Consulta al servidor cada 30 segundos (solo si la pestaña está visible)
            setInterval(() => {
                if (!document.hidden) this.actualizar();
            }, 30000);
        },
        async actualizar() {
            try {
                const datos = await pedir(url);
                if (datos.sin_leer > this.sinLeer) {
                    this.sacudir = true;
                    setTimeout(() => (this.sacudir = false), 900);
                    window.avisar(datos.items[0]?.titulo ? `Nueva notificación: ${datos.items[0].titulo}` : 'Tienes una notificación nueva', 'info');
                }
                this.sinLeer = datos.sin_leer;
                this.items = datos.items;
            } catch {
                // Sin conexión: se vuelve a intentar en la siguiente vuelta
            }
        },
    }));

    /* ---------- Guardar vacante sin recargar ---------- */
    Alpine.data('guardarVacante', (url, guardadaInicial) => ({
        guardada: guardadaInicial,
        rebotar: false,
        enviando: false,
        async alternar() {
            if (this.enviando) return;
            this.enviando = true;
            try {
                const datos = await pedir(url, { method: 'POST' });
                this.guardada = datos.guardada;
                this.rebotar = true;
                setTimeout(() => (this.rebotar = false), 450);
                window.avisar(datos.mensaje);
            } catch (e) {
                window.avisar(e.message, 'error');
            } finally {
                this.enviando = false;
            }
        },
    }));

    /* ---------- Cambiar estado de una postulación sin recargar ---------- */
    Alpine.data('estadoPostulacion', (url, actual) => ({
        estado: actual,
        anterior: actual,
        enviando: false,
        async cambiar() {
            if (this.estado === 'contratado' && !confirm('¿Marcar como contratado?')) {
                this.estado = this.anterior;
                return;
            }
            this.enviando = true;
            try {
                const datos = await pedir(url, { method: 'PATCH', body: JSON.stringify({ estado: this.estado }) });
                this.anterior = this.estado;
                window.avisar(datos.mensaje);
            } catch (e) {
                this.estado = this.anterior;
                window.avisar(e.message, 'error');
            } finally {
                this.enviando = false;
            }
        },
    }));
}
