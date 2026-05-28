ALTER TABLE citas
  ADD COLUMN recomendaciones TEXT DEFAULT NULL AFTER nota,
  ADD COLUMN comentarios TEXT DEFAULT NULL AFTER recomendaciones;
