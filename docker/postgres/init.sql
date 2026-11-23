-- Se ejecuta solo la primera vez que se crea el contenedor (volumen vacío).
-- Crea la base de datos que usan los tests automáticos (phpunit.xml).
CREATE DATABASE jobmatch_test OWNER jobmatch;
