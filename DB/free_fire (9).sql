-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-11-2025 a las 17:27:31
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `free_fire`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `armas`
--

CREATE TABLE `armas` (
  `id_armas` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `dano_cabeza` int(11) NOT NULL,
  `dano_cuerpo` int(11) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `id_tipo_arma` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `armas`
--

INSERT INTO `armas` (`id_armas`, `nombre`, `dano_cabeza`, `dano_cuerpo`, `imagen`, `id_tipo_arma`) VALUES
(9, 'Puño', 5, 3, 'IMG/puño.png', 1),
(10, 'Katana', 12, 6, 'IMG/katana.png', 1),
(11, 'G18', 15, 10, 'IMG/g18.png', 2),
(12, 'Desert Eagle', 20, 15, 'IMG/desert_eagle.png', 2),
(13, 'AWM', 75, 50, 'IMG/awm.png', 3),
(14, 'M82B', 75, 50, 'IMG/m82b.png', 3),
(15, 'M249', 50, 40, 'IMG/m249.png', 4),
(16, 'KORD', 50, 40, 'IMG/kord.png', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_partida`
--

CREATE TABLE `detalle_partida` (
  `id_detalle_partida` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_partidas` int(11) NOT NULL,
  `id_armas` int(11) NOT NULL,
  `dano_causado` int(11) NOT NULL,
  `dano_recibido` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado`
--

CREATE TABLE `estado` (
  `id_estado` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado`
--

INSERT INTO `estado` (`id_estado`, `nombre`) VALUES
(1, 'activo'),
(2, 'bloqueado'),
(3, 'en juego'),
(4, 'en espera'),
(5, 'llena');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mapa`
--

CREATE TABLE `mapa` (
  `id_mapa` int(11) NOT NULL,
  `nombre` text NOT NULL,
  `descripcion` varchar(60) NOT NULL,
  `imagen` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mapa`
--

INSERT INTO `mapa` (`id_mapa`, `nombre`, `descripcion`, `imagen`) VALUES
(1, 'Bermuda', 'Una isla inspirada en el Triángulo de las Bermudas, que comb', '/img/mapas/bermuda.jpeg'),
(2, 'Purgatorio', 'Mapa grande con un terreno variado de montañas, lagos y un r', '/img/mapas/purgatorio.webp'),
(3, 'kalahari', 'Mapa desértico caracterizado por su terreno rojizo y estruct', '/img/mapas/kalahari.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modos_juegos`
--

CREATE TABLE `modos_juegos` (
  `id_modo_juegos` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `modos_juegos`
--

INSERT INTO `modos_juegos` (`id_modo_juegos`, `nombre`, `descripcion`, `tipo`) VALUES
(1, 'BR-Clasificatoria', 'Mapa abierto de maximo 5 jugadores ', NULL),
(2, 'DE-Clasificatoria', '4v4 en mapas pequeños', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `niveles`
--

CREATE TABLE `niveles` (
  `id_niveles` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `niveles`
--

INSERT INTO `niveles` (`id_niveles`, `nombre`) VALUES
(1, 'oro'),
(2, 'plantino'),
(3, 'diamante'),
(4, 'heroico'),
(5, 'maestro');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partidas`
--

CREATE TABLE `partidas` (
  `id_partida` int(11) NOT NULL,
  `id_sala` int(11) NOT NULL,
  `fecha_inicio` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_fin` datetime DEFAULT NULL,
  `ganador` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `partidas`
--

INSERT INTO `partidas` (`id_partida`, `id_sala`, `fecha_inicio`, `fecha_fin`, `ganador`) VALUES
(1, 21, '2025-11-05 09:34:41', NULL, NULL),
(2, 20, '2025-11-05 10:42:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partidas_old`
--

CREATE TABLE `partidas_old` (
  `id_partida` int(11) NOT NULL,
  `id_sala` int(11) DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL,
  `Ganador` int(11) NOT NULL,
  `finalizada` tinyint(1) DEFAULT 0,
  `fecha_fin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `partidas_old`
--

INSERT INTO `partidas_old` (`id_partida`, `id_sala`, `fecha_inicio`, `Ganador`, `finalizada`, `fecha_fin`) VALUES
(1, 20, '2025-11-05 07:50:37', 0, 0, NULL),
(2, 21, '2025-11-05 08:58:12', 0, 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personajes`
--

CREATE TABLE `personajes` (
  `Id_personajes` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `skin` varchar(250) NOT NULL,
  `descripcion` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personajes`
--

INSERT INTO `personajes` (`Id_personajes`, `nombre`, `skin`, `descripcion`) VALUES
(1, 'Alok', 'IMG/personaje_alok.png', 'Alok, es un personaje de apoyo con la habilidad \"Ritmo brutal\", que crea un aura que aumenta la velocidad de movimiento y restaura pv para el y sus compañeros de equipo. Su nombre significa \"luz\" y vino al juego para dar un concierto especial.'),
(2, 'kapella', 'IMG/personaje_kapella.png\r\n', 'Kapella es una cantante de pop de garena free fire, conocida por su habilidad especial llamada cancion curativa, que aumenta los efectos de los objetos y habilidades de curacion y reduce la perdida de PV de los aliados.\r\n'),
(3, 'Wukong', 'IMG/personajes_wukong.png', 'Wukong, también conocido como el Rey Mono, es una figura central en la mitología china, nacido de una piedra y conocido por su rebelión contra el cielo. Es un mono con una amplia gama de poderes, incluyendo habilidades de combate, 72 transformaciones terrestres, la capacidad de replicar su cabello mágico y trucos como volverse invisible o manipular el clima.'),
(4, 'Steffie', 'IMG/personajes_steffie.png', 'Steffie puede referirse a varias personas de diferentes franquicias, como el personaje de Steffie (Free Fire), una artista rebelde que crea grafitis y usa la habilidad de \"Refugio de Pintura\". ');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas_pascal`
--

CREATE TABLE `preguntas_pascal` (
  `id` int(11) NOT NULL,
  `pregunta` text NOT NULL,
  `opcion_a` varchar(255) NOT NULL,
  `opcion_b` varchar(255) NOT NULL,
  `opcion_c` varchar(255) NOT NULL,
  `correcta` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `preguntas_pascal`
--

INSERT INTO `preguntas_pascal` (`id`, `pregunta`, `opcion_a`, `opcion_b`, `opcion_c`, `correcta`) VALUES
(1, '¿Qué palabra clave se usa para comenzar un programa en Pascal?', 'Program', 'Start', 'Begin', 'A'),
(2, '¿Cómo se declara una variable entera en Pascal?', 'Int x;', 'Var x: integer;', 'Integer x;', 'B'),
(3, '¿Qué instrucción se usa para escribir texto en pantalla?', 'Display;', 'Print;', 'Write;', 'C'),
(4, '¿Con qué palabra finaliza un bloque en Pascal?', 'End;', 'Finish;', 'Stop;', 'A'),
(5, '¿Cuál es la extensión de los archivos fuente de Pascal?', '.pas', '.psc', '.pcl', 'A');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sala`
--

CREATE TABLE `sala` (
  `id_sala` int(11) NOT NULL,
  `id_modo_juegos` int(11) DEFAULT NULL,
  `id_niveles` int(11) DEFAULT NULL,
  `id_mapa` int(11) NOT NULL,
  `id_estado` int(11) DEFAULT NULL,
  `jugadores_actuales` int(11) DEFAULT 1,
  `max_jugadores` int(11) DEFAULT 5,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_inicio_timer` timestamp NULL DEFAULT NULL,
  `estado_partida` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sala`
--

INSERT INTO `sala` (`id_sala`, `id_modo_juegos`, `id_niveles`, `id_mapa`, `id_estado`, `jugadores_actuales`, `max_jugadores`, `fecha_creacion`, `fecha_inicio_timer`, `estado_partida`) VALUES
(19, 1, 2, 3, 4, 0, 5, '2025-10-31 14:39:21', NULL, 0),
(20, 1, 1, 1, 2, 7, 5, '2025-10-31 15:28:09', NULL, 1),
(21, 1, 1, 3, 1, 2, 5, '2025-11-04 20:59:59', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_armas`
--

CREATE TABLE `tipo_armas` (
  `id_tipo_arma` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_armas`
--

INSERT INTO `tipo_armas` (`id_tipo_arma`, `nombre`, `tipo`) VALUES
(1, 'Puño', 'Cuerpo a cuerpo'),
(2, 'Pistola', 'Corta distancia'),
(3, 'Francotirador', 'Larga distancia'),
(4, 'Ametralladora', 'Pesada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tip_user`
--

CREATE TABLE `tip_user` (
  `id_tip_user` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tip_user`
--

INSERT INTO `tip_user` (`id_tip_user`, `tipo`) VALUES
(1, 'admin'),
(2, 'jugador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_user` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `nombre` text NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `puntos` int(11) DEFAULT 0,
  `id_niveles` int(11) DEFAULT NULL,
  `id_tip_user` int(11) DEFAULT NULL,
  `Id_personajes` int(11) DEFAULT 1,
  `id_estado` int(11) DEFAULT NULL,
  `ultima_conexion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_user`, `username`, `nombre`, `correo`, `contrasena`, `puntos`, `id_niveles`, `id_tip_user`, `Id_personajes`, `id_estado`, `ultima_conexion`) VALUES
(312123231, 'dd', '1231231', 'asda@gmail.com', '$2y$10$W2c/L7WX7910AVJrZlP.QOKdAQ.ch1.8gH.611YoZh4fWRdmNkSNi', 0, 1, 2, 1, 1, '2025-10-08 21:06:58'),
(1032500012, 'jorge_fire', '', 'jorge_fire@gmail.com', '', 800, NULL, NULL, 1, 1, '2025-10-29 20:15:10'),
(1045896312, 'maria_sniper', '', 'maria_sniper@gmail.com', '', 1200, NULL, NULL, 1, 1, '2025-10-30 15:50:00'),
(1098745632, 'kevin_ff', '', 'kevinff99@gmail.com', '', 450, NULL, NULL, 1, 2, '2025-10-25 18:22:33'),
(1101239987, 'luna_heroic', '', 'luna_heroic@gmail.com', '', 2300, NULL, NULL, 1, 1, '2025-10-31 22:40:15'),
(1110495789, 'dires123', 'Didier Reyes', 'didierreyes003@gmail.com', '$2y$10$qOd5sC/ZP4ag01dLDO0/QuhJ4JD1muGxZpZVXeArdp9hi9FcndJ4K', 600, 2, 1, 4, 1, '2025-10-08 20:12:40'),
(1121212312, 'juanito', 'juan', 'reyesz2803@gmail.com', '$2y$10$Gp4Wez/J8ys8gboVx0yPiOfVgp5oeuuVrupeeOlssQ4B3ruv/Y/J6', 600, 1, 2, 1, 1, '2025-10-08 20:36:10'),
(1124578963, 'pedro_max', '', 'pedro_max@gmail.com', '', 1500, NULL, NULL, 1, 1, '2025-10-29 17:10:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_sala`
--

CREATE TABLE `usuario_sala` (
  `id_usuario_sala` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_sala` int(11) NOT NULL,
  `tiempo_entrada` timestamp NOT NULL DEFAULT current_timestamp(),
  `eliminado` tinyint(1) DEFAULT 0,
  `vida` int(11) DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario_sala`
--

INSERT INTO `usuario_sala` (`id_usuario_sala`, `id_user`, `id_sala`, `tiempo_entrada`, `eliminado`, `vida`) VALUES
(6, 1110495789, 17, '2025-10-29 19:43:50', 1, 100),
(7, 1110495789, 17, '2025-10-29 20:51:59', 1, 100),
(8, 1110495789, 17, '2025-10-29 20:52:13', 1, 100),
(9, 1110495789, 17, '2025-10-29 20:56:01', 1, 100),
(10, 1121212312, 17, '2025-10-29 20:56:06', 1, 100),
(11, 1110495789, 17, '2025-10-29 21:13:25', 1, 100),
(12, 1121212312, 17, '2025-10-29 21:16:15', 1, 100),
(13, 1110495789, 18, '2025-10-31 19:37:48', 1, 100),
(14, 1110495789, 19, '2025-10-31 19:39:25', 1, 100),
(15, 1110495789, 19, '2025-10-31 20:28:11', 1, 100),
(16, 1110495789, 19, '2025-10-31 20:40:20', 1, 100),
(17, 1121212312, 20, '2025-10-31 20:41:08', 0, 100),
(18, 1110495789, 19, '2025-10-31 20:41:17', 1, 100),
(19, 1110495789, 20, '2025-10-31 20:41:57', 1, 94),
(20, 1121212312, 20, '2025-10-31 20:42:03', 0, 100),
(21, 1110495789, 20, '2025-10-31 20:56:23', 1, 94),
(22, 1121212312, 20, '2025-10-31 20:56:35', 0, 100),
(23, 1110495789, 19, '2025-11-05 00:59:06', 1, 100),
(24, 1110495789, 19, '2025-11-05 01:10:09', 1, 100),
(25, 1110495789, 19, '2025-11-05 01:10:35', 1, 100),
(26, 1121212312, 20, '2025-11-05 01:36:37', 0, 100),
(27, 1110495789, 19, '2025-11-05 07:36:51', 1, 100),
(28, 1110495789, 20, '2025-11-05 07:37:39', 1, 94),
(29, 1121212312, 20, '2025-11-05 07:37:44', 0, 100),
(30, 1110495789, 20, '2025-11-05 12:50:34', 1, 94),
(31, 1121212312, 20, '2025-11-05 12:51:56', 0, 100),
(32, 1110495789, 20, '2025-11-05 13:40:06', 1, 94),
(33, 1110495789, 20, '2025-11-05 13:40:37', 1, 94),
(34, 1121212312, 21, '2025-11-05 13:52:12', 1, 100),
(35, 1110495789, 21, '2025-11-05 13:52:19', 1, 10),
(36, 312123231, 21, '2025-11-05 15:22:40', 1, 100),
(37, 312123231, 21, '2025-11-05 15:33:44', 0, 100),
(38, 1110495789, 21, '2025-11-05 15:34:06', 1, 100),
(39, 1110495789, 19, '2025-11-05 15:35:53', 1, 100),
(40, 1110495789, 21, '2025-11-05 15:36:46', 1, 100),
(41, 1110495789, 20, '2025-11-05 15:41:51', 1, 94),
(42, 312123231, 20, '2025-11-05 15:42:05', 0, 100),
(43, 1110495789, 21, '2025-11-05 15:42:53', 1, 100),
(44, 1110495789, 20, '2025-11-05 15:43:05', 1, 100),
(45, 1110495789, 21, '2025-11-05 15:43:39', 0, 100);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `armas`
--
ALTER TABLE `armas`
  ADD PRIMARY KEY (`id_armas`),
  ADD KEY `id_tipo_arma` (`id_tipo_arma`);

--
-- Indices de la tabla `detalle_partida`
--
ALTER TABLE `detalle_partida`
  ADD PRIMARY KEY (`id_detalle_partida`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_partidas` (`id_partidas`),
  ADD KEY `id_armas` (`id_armas`);

--
-- Indices de la tabla `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`id_estado`);

--
-- Indices de la tabla `mapa`
--
ALTER TABLE `mapa`
  ADD PRIMARY KEY (`id_mapa`);

--
-- Indices de la tabla `modos_juegos`
--
ALTER TABLE `modos_juegos`
  ADD PRIMARY KEY (`id_modo_juegos`);

--
-- Indices de la tabla `niveles`
--
ALTER TABLE `niveles`
  ADD PRIMARY KEY (`id_niveles`);

--
-- Indices de la tabla `partidas`
--
ALTER TABLE `partidas`
  ADD PRIMARY KEY (`id_partida`),
  ADD KEY `idx_id_sala` (`id_sala`),
  ADD KEY `idx_ganador` (`ganador`);

--
-- Indices de la tabla `partidas_old`
--
ALTER TABLE `partidas_old`
  ADD PRIMARY KEY (`id_partida`),
  ADD KEY `id_sala` (`id_sala`);

--
-- Indices de la tabla `personajes`
--
ALTER TABLE `personajes`
  ADD PRIMARY KEY (`Id_personajes`);

--
-- Indices de la tabla `preguntas_pascal`
--
ALTER TABLE `preguntas_pascal`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sala`
--
ALTER TABLE `sala`
  ADD PRIMARY KEY (`id_sala`),
  ADD KEY `id_modo_juegos` (`id_modo_juegos`),
  ADD KEY `id_niveles` (`id_niveles`),
  ADD KEY `id_mapa` (`id_mapa`),
  ADD KEY `id_estado` (`id_estado`);

--
-- Indices de la tabla `tipo_armas`
--
ALTER TABLE `tipo_armas`
  ADD PRIMARY KEY (`id_tipo_arma`);

--
-- Indices de la tabla `tip_user`
--
ALTER TABLE `tip_user`
  ADD PRIMARY KEY (`id_tip_user`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `id_niveles` (`id_niveles`),
  ADD KEY `id_tip_user` (`id_tip_user`),
  ADD KEY `Id_personajes` (`Id_personajes`),
  ADD KEY `id_estado` (`id_estado`);

--
-- Indices de la tabla `usuario_sala`
--
ALTER TABLE `usuario_sala`
  ADD PRIMARY KEY (`id_usuario_sala`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `armas`
--
ALTER TABLE `armas`
  MODIFY `id_armas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `estado`
--
ALTER TABLE `estado`
  MODIFY `id_estado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `mapa`
--
ALTER TABLE `mapa`
  MODIFY `id_mapa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `modos_juegos`
--
ALTER TABLE `modos_juegos`
  MODIFY `id_modo_juegos` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `niveles`
--
ALTER TABLE `niveles`
  MODIFY `id_niveles` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `partidas`
--
ALTER TABLE `partidas`
  MODIFY `id_partida` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `partidas_old`
--
ALTER TABLE `partidas_old`
  MODIFY `id_partida` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `personajes`
--
ALTER TABLE `personajes`
  MODIFY `Id_personajes` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `preguntas_pascal`
--
ALTER TABLE `preguntas_pascal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `sala`
--
ALTER TABLE `sala`
  MODIFY `id_sala` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `tipo_armas`
--
ALTER TABLE `tipo_armas`
  MODIFY `id_tipo_arma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tip_user`
--
ALTER TABLE `tip_user`
  MODIFY `id_tip_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1124578964;

--
-- AUTO_INCREMENT de la tabla `usuario_sala`
--
ALTER TABLE `usuario_sala`
  MODIFY `id_usuario_sala` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_partida`
--
ALTER TABLE `detalle_partida`
  ADD CONSTRAINT `detalle_partida_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `usuario` (`id_user`),
  ADD CONSTRAINT `detalle_partida_ibfk_2` FOREIGN KEY (`id_partidas`) REFERENCES `partidas_old` (`id_partida`),
  ADD CONSTRAINT `detalle_partida_ibfk_3` FOREIGN KEY (`id_armas`) REFERENCES `armas` (`id_armas`);

--
-- Filtros para la tabla `partidas`
--
ALTER TABLE `partidas`
  ADD CONSTRAINT `fk_partidas_ganador` FOREIGN KEY (`ganador`) REFERENCES `usuario` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_partidas_sala` FOREIGN KEY (`id_sala`) REFERENCES `sala` (`id_sala`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `partidas_ibfk_1` FOREIGN KEY (`id_sala`) REFERENCES `sala` (`id_sala`),
  ADD CONSTRAINT `partidas_ibfk_2` FOREIGN KEY (`ganador`) REFERENCES `usuario` (`id_user`);

--
-- Filtros para la tabla `partidas_old`
--
ALTER TABLE `partidas_old`
  ADD CONSTRAINT `partidas_old_ibfk_1` FOREIGN KEY (`id_sala`) REFERENCES `sala` (`id_sala`);

--
-- Filtros para la tabla `sala`
--
ALTER TABLE `sala`
  ADD CONSTRAINT `sala_ibfk_1` FOREIGN KEY (`id_modo_juegos`) REFERENCES `modos_juegos` (`id_modo_juegos`),
  ADD CONSTRAINT `sala_ibfk_2` FOREIGN KEY (`id_niveles`) REFERENCES `niveles` (`id_niveles`),
  ADD CONSTRAINT `sala_ibfk_3` FOREIGN KEY (`id_mapa`) REFERENCES `mapa` (`id_mapa`),
  ADD CONSTRAINT `sala_ibfk_4` FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id_estado`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_tip_user`) REFERENCES `tip_user` (`id_tip_user`),
  ADD CONSTRAINT `usuario_ibfk_2` FOREIGN KEY (`id_niveles`) REFERENCES `niveles` (`id_niveles`),
  ADD CONSTRAINT `usuario_ibfk_3` FOREIGN KEY (`Id_personajes`) REFERENCES `personajes` (`Id_personajes`),
  ADD CONSTRAINT `usuario_ibfk_4` FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id_estado`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
