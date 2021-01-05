SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for as_users
-- ----------------------------
DROP TABLE IF EXISTS `as_users`;
CREATE TABLE `as_users` (
  `uid` int(10) unsigned NOT NULL,
  `name` char(31) NOT NULL DEFAULT '',
  `flags` char(26) NOT NULL DEFAULT '',
  `signature` char(63) NOT NULL DEFAULT '',
  `authid` char(32) NOT NULL DEFAULT '',
  `password` char(32) NOT NULL DEFAULT '',
  `salt` char(6) NOT NULL DEFAULT '',
  `regtimestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `lasttimestamp` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00',
  `lastipaddress` char(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for as_fights
-- ----------------------------
DROP TABLE IF EXISTS `as_fights`;
CREATE TABLE `as_fights` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mode` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `map` char(32) NOT NULL,
  `uid_a` int(10) unsigned NOT NULL,
  `name_a` char(31) NOT NULL DEFAULT '',
  `team_a` char(1) NOT NULL DEFAULT '0',
  `health_a` int(11) NOT NULL,
  `wid_a` tinyint(3) unsigned NOT NULL,
  `uid_v` int(10) unsigned NOT NULL,
  `name_v` char(31) NOT NULL DEFAULT '',
  `team_v` char(1) NOT NULL DEFAULT '0',
  `health_v` int(11) NOT NULL,
  `wid_v` tinyint(3) unsigned NOT NULL,
  `aiming` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `damage` int(10) unsigned NOT NULL DEFAULT 0,
  `damage_real` int(10) unsigned NOT NULL DEFAULT 0,
  `kflag` char(1) NOT NULL DEFAULT '0',
  `wflag` char(1) NOT NULL DEFAULT '0',
  `tflag` char(1) NOT NULL DEFAULT '0',
  `distance` float unsigned NOT NULL DEFAULT 0,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `mark` char(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mode` (`mode`),
  KEY `map` (`map`),
  KEY `uida` (`uid_a`),
  KEY `uidv` (`uid_v`),
  KEY `kflag` (`kflag`),
  KEY `wflag` (`wflag`),
  KEY `tflag` (`tflag`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for as_map_rankings
-- ----------------------------
DROP TABLE IF EXISTS `as_map_rankings`;
CREATE TABLE `as_map_rankings` (
  `mode` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `map` char(32) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `score` decimal(16,6) NOT NULL DEFAULT 0.000000,
  `scoreRanking` int(10) unsigned NOT NULL DEFAULT 100000000,
  `rating` decimal(16,6) NOT NULL DEFAULT 1.000000,
  `ratingRanking` int(10) unsigned NOT NULL DEFAULT 100000000,
  `rws` decimal(16,6) NOT NULL DEFAULT 0.000000,
  `rwsRanking` int(10) unsigned NOT NULL DEFAULT 100000000,
  PRIMARY KEY (`mode`, `map`,`uid`),
  KEY `mode` (`mode`),
  KEY `map` (`map`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for as_map_bombs
-- ----------------------------
DROP TABLE IF EXISTS `as_map_bombs`;
CREATE TABLE `as_map_bombs` (
  `mode` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `map` char(32) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `planting` int(10) unsigned NOT NULL DEFAULT 0,
  `planted` int(10) unsigned NOT NULL DEFAULT 0,
  `explode` int(10) unsigned NOT NULL DEFAULT 0,
  `defusing` int(10) unsigned NOT NULL DEFAULT 0,
  `defused` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`mode`, `map`,`uid`),
  KEY `mode` (`mode`),
  KEY `map` (`map`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for as_map_rounds
-- ----------------------------
DROP TABLE IF EXISTS `as_map_rounds`;
CREATE TABLE `as_map_rounds` (
  `mode` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `map` char(32) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `unassigned` int(10) unsigned NOT NULL DEFAULT 0,
  `t` int(10) unsigned NOT NULL DEFAULT 0,
  `ct` int(10) unsigned NOT NULL DEFAULT 0,
  `spectator` int(10) unsigned NOT NULL DEFAULT 0,
  `win_t` int(10) unsigned NOT NULL DEFAULT 0,
  `win_ct` int(10) unsigned NOT NULL DEFAULT 0,
  `win_shares` int(10) unsigned NOT NULL DEFAULT 0,
  `first_kill` int(10) unsigned NOT NULL DEFAULT 0,
  `first_death` int(10) unsigned NOT NULL DEFAULT 0,
  `last_kill` int(10) unsigned NOT NULL DEFAULT 0,
  `last_death` int(10) unsigned NOT NULL DEFAULT 0,
  `mvp` int(10) unsigned NOT NULL DEFAULT 0,
  `kast` int(10) unsigned NOT NULL DEFAULT 0,
  `assist` int(10) unsigned NOT NULL DEFAULT 0,
  `assist_by_damage` int(10) unsigned NOT NULL DEFAULT 0,
  `assist_by_flashbang` int(10) unsigned NOT NULL DEFAULT 0,
  `kill_1` int(10) unsigned NOT NULL DEFAULT 0,
  `kill_2` int(10) unsigned NOT NULL DEFAULT 0,
  `kill_3` int(10) unsigned NOT NULL DEFAULT 0,
  `kill_4` int(10) unsigned NOT NULL DEFAULT 0,
  `kill_5` int(10) unsigned NOT NULL DEFAULT 0,
  `1_vs_1` int(10) unsigned NOT NULL DEFAULT 0,
  `1_vs_2` int(10) unsigned NOT NULL DEFAULT 0,
  `1_vs_3` int(10) unsigned NOT NULL DEFAULT 0,
  `1_vs_4` int(10) unsigned NOT NULL DEFAULT 0,
  `1_vs_5` int(10) unsigned NOT NULL DEFAULT 0,
  `time` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`mode`, `map`,`uid`),
  KEY `mode` (`mode`),
  KEY `map` (`map`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for as_map_weapons
-- ----------------------------
DROP TABLE IF EXISTS `as_map_weapons`;
CREATE TABLE `as_map_weapons` (
  `mode` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `map` char(32) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `wid` tinyint(3) unsigned NOT NULL,
  `tflag` char(1) NOT NULL,
  `kill` int(10) unsigned NOT NULL DEFAULT 0,
  `kill_hs` int(10) unsigned NOT NULL DEFAULT 0,
  `kill_ws` int(10) unsigned NOT NULL DEFAULT 0,
  `kill_hws` int(10) unsigned NOT NULL DEFAULT 0,
  `killed` int(10) unsigned NOT NULL DEFAULT 0,
  `killed_hs` int(10) unsigned NOT NULL DEFAULT 0,
  `killed_ws` int(10) unsigned NOT NULL DEFAULT 0,
  `killed_hws` int(10) unsigned NOT NULL DEFAULT 0,
  `death` int(10) unsigned NOT NULL DEFAULT 0,
  `shot` int(10) unsigned NOT NULL DEFAULT 0,
  `hit` int(10) unsigned NOT NULL DEFAULT 0,
  `hit_hs` int(10) unsigned NOT NULL DEFAULT 0,
  `hit_ws` int(10) unsigned NOT NULL DEFAULT 0,
  `hit_hws` int(10) unsigned NOT NULL DEFAULT 0,
  `damage` int(10) unsigned NOT NULL DEFAULT 0,
  `damage_real` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`mode`, `map`,`uid`,`wid`,`tflag`) USING BTREE,
  KEY `mode` (`mode`),
  KEY `map` (`map`),
  KEY `uid` (`uid`),
  KEY `wid` (`wid`),
  KEY `tflag` (`tflag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for as_map_bodyhits
-- ----------------------------
DROP TABLE IF EXISTS `as_map_bodyhits`;
CREATE TABLE `as_map_bodyhits` (
  `mode` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `map` char(32) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `wid` tinyint(3) unsigned NOT NULL,
  `tflag` char(1) NOT NULL,
  `generic` int(10) unsigned NOT NULL DEFAULT 0,
  `head` int(10) unsigned NOT NULL DEFAULT 0,
  `chest` int(10) unsigned NOT NULL DEFAULT 0,
  `stomach` int(10) unsigned NOT NULL DEFAULT 0,
  `leftarm` int(10) unsigned NOT NULL DEFAULT 0,
  `rightarm` int(10) unsigned NOT NULL DEFAULT 0,
  `leftleg` int(10) unsigned NOT NULL DEFAULT 0,
  `rightleg` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`mode`, `map`,`uid`,`wid`,`tflag`),
  KEY `mode` (`mode`),
  KEY `map` (`map`),
  KEY `uid` (`uid`),
  KEY `wid` (`wid`),
  KEY `tflag` (`tflag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for as_all_rankings
-- ----------------------------
DROP TABLE IF EXISTS `as_all_rankings`;
CREATE TABLE `as_all_rankings` (
  `mode` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `uid` int(10) unsigned NOT NULL,
  `score` decimal(16,6) NOT NULL DEFAULT 0.000000,
  `scoreRanking` int(10) unsigned NOT NULL DEFAULT 100000000,
  `rating` decimal(16,6) NOT NULL DEFAULT 1.000000,
  `ratingRanking` int(10) unsigned NOT NULL DEFAULT 100000000,
  `rws` decimal(16,6) NOT NULL DEFAULT 0.000000,
  `rwsRanking` int(10) unsigned NOT NULL DEFAULT 100000000,
  PRIMARY KEY (`mode`, `uid`),
  KEY `mode` (`mode`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS=1;
