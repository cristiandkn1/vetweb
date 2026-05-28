-- Agrega token_publico a mascota para URLs públicas únicas
ALTER TABLE mascota
  ADD COLUMN token_publico CHAR(48) DEFAULT NULL AFTER fecha_actualizacion,
  ADD UNIQUE KEY uq_mascota_token (token_publico);

-- Backfill: generar token para mascotas existentes que no tengan uno
UPDATE mascota SET token_publico = LOWER(CONCAT(
    HEX(RAND() * 0xFFFFFFFF),
    HEX(RAND() * 0xFFFFFFFF),
    HEX(RAND() * 0xFFFFFFFF),
    HEX(RAND() * 0xFFFFFFFF),
    HEX(RAND() * 0xFFFFFFFF),
    HEX(RAND() * 0xFFFFFFFF)
)) WHERE token_publico IS NULL;
