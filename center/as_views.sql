-- ----------------------------
-- View structure for as_map_weapons_sum
-- ----------------------------
DROP VIEW IF EXISTS `as_map_weapons_sum`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER  VIEW `as_map_weapons_sum` AS select
`mode`,
`map`,
`uid`,
`tflag`,
sum(`kill`) as `kill`,
sum(`kill_hs`) as `kill_hs`,
sum(`kill_ws`) as `kill_ws`,
sum(`kill_hws`) as `kill_hws`,
sum(`killed`) as `killed`,
sum(`killed_hs`) as `killed_hs`,
sum(`killed_ws`) as `killed_ws`,
sum(`killed_hws`) as `killed_hws`,
sum(`death`) as `death`,
sum(`shot`) as `shot`,
sum(`hit`) as `hit`,
sum(`hit_hs`) as `hit_hs`,
sum(`hit_ws`) as `hit_ws`,
sum(`hit_hws`) as `hit_hws`,
sum(`damage`) as `damage`,
sum(`damage_real`) as `damage_real`
from `as_map_weapons`
group by `mode`, `map`, `uid`, `tflag`;

-- ----------------------------
-- View structure for as_map_bodyhits_sum
-- ----------------------------
DROP VIEW IF EXISTS `as_map_bodyhits_sum`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER  VIEW `as_map_bodyhits_sum` AS select
`mode`,
`map`,
`uid`,
`tflag`,
sum(`generic`) as `generic`,
sum(`head`) as `head`,
sum(`chest`) as `chest`,
sum(`stomach`) as `stomach`,
sum(`leftarm`) as `leftarm`,
sum(`rightarm`) as `rightarm`,
sum(`leftleg`) as `leftleg`,
sum(`rightleg`) as `rightleg`
from `as_map_bodyhits`
group by `mode`, `map`, `uid`, `tflag`;

-- ----------------------------
-- View structure for as_map_stats
-- ----------------------------
DROP VIEW IF EXISTS `as_map_stats`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER  VIEW `as_map_stats` AS select
`r`.`mode`,
`r`.`map`,
`r`.`uid`,
`b`.`planting`,
`b`.`planted`,
`b`.`explode`,
`b`.`defusing`,
`b`.`defused`,
`r`.`unassigned`,
`r`.`t`,
`r`.`ct`,
`r`.`spectator`,
`r`.`win_t`,
`r`.`win_ct`,
`r`.`win_shares`,
`r`.`first_kill`,
`r`.`first_death`,
`r`.`last_kill`,
`r`.`last_death`,
`r`.`mvp`,
`r`.`kast`,
`r`.`assist`,
`r`.`assist_by_damage`,
`r`.`assist_by_flashbang`,
`r`.`kill_1`,
`r`.`kill_2`,
`r`.`kill_3`,
`r`.`kill_4`,
`r`.`kill_5`,
`r`.`1_vs_1`,
`r`.`1_vs_2`,
`r`.`1_vs_3`,
`r`.`1_vs_4`,
`r`.`1_vs_5`,
`r`.`time`,
`w`.`kill`,
`w`.`kill_hs`,
`w`.`kill_ws`,
`w`.`kill_hws`,
`w`.`killed`,
`w`.`killed_hs`,
`w`.`killed_ws`,
`w`.`killed_hws`,
`w`.`death`,
`w`.`shot`,
`w`.`hit`,
`w`.`hit_hs`,
`w`.`hit_ws`,
`w`.`hit_hws`,
`w`.`damage`,
`w`.`damage_real`
from `as_map_bombs` as `b` right join `as_map_rounds` as `r` on `r`.`mode` = `b`.`mode` and `r`.`map` = `b`.`map` and `r`.`uid` = `b`.`uid` inner join `as_map_weapons_sum` as `w` on `w`.`tflag` = '0' and `w`.`mode` = `r`.`mode` and `w`.`map` = `r`.`map` and `w`.`uid` = `r`.`uid`;

-- ----------------------------
-- View structure for as_all_bombs
-- ----------------------------
DROP VIEW IF EXISTS `as_all_bombs`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER  VIEW `as_all_bombs` AS select
`mode`,
`uid`,
sum(`planting`) as `planting`,
sum(`planted`) as `planted`,
sum(`explode`) as `explode`,
sum(`defusing`) as `defusing`,
sum(`defused`) as `defused`
from `as_map_bombs`
group by `mode`, `uid`;

-- ----------------------------
-- View structure for as_all_rounds
-- ----------------------------
DROP VIEW IF EXISTS `as_all_rounds`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER  VIEW `as_all_rounds` AS select
`mode`,
`uid`,
sum(`unassigned`) as `unassigned`,
sum(`t`) as `t`,
sum(`ct`) as `ct`,
sum(`spectator`) as `spectator`,
sum(`win_t`) as `win_t`,
sum(`win_ct`) as `win_ct`,
sum(`win_shares`) as `win_shares`,
sum(`first_kill`) as `first_kill`,
sum(`first_death`) as `first_death`,
sum(`last_kill`) as `last_kill`,
sum(`last_death`) as `last_death`,
sum(`mvp`) as `mvp`,
sum(`kast`) as `kast`,
sum(`assist`) as `assist`,
sum(`assist_by_damage`) as `assist_by_damage`,
sum(`assist_by_flashbang`) as `assist_by_flashbang`,
sum(`kill_1`) as `kill_1`,
sum(`kill_2`) as `kill_2`,
sum(`kill_3`) as `kill_3`,
sum(`kill_4`) as `kill_4`,
sum(`kill_5`) as `kill_5`,
sum(`1_vs_1`) as `1_vs_1`,
sum(`1_vs_2`) as `1_vs_2`,
sum(`1_vs_3`) as `1_vs_3`,
sum(`1_vs_4`) as `1_vs_4`,
sum(`1_vs_5`) as `1_vs_5`,
sum(`time`) as `time`
from `as_map_rounds`
group by `mode`, `uid`;

-- ----------------------------
-- View structure for as_all_weapons
-- ----------------------------
DROP VIEW IF EXISTS `as_all_weapons`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER  VIEW `as_all_weapons` AS select
`mode`,
`uid`,
`wid`,
`tflag`,
sum(`kill`) as `kill`,
sum(`kill_hs`) as `kill_hs`,
sum(`kill_ws`) as `kill_ws`,
sum(`kill_hws`) as `kill_hws`,
sum(`killed`) as `killed`,
sum(`killed_hs`) as `killed_hs`,
sum(`killed_ws`) as `killed_ws`,
sum(`killed_hws`) as `killed_hws`,
sum(`death`) as `death`,
sum(`shot`) as `shot`,
sum(`hit`) as `hit`,
sum(`hit_hs`) as `hit_hs`,
sum(`hit_ws`) as `hit_ws`,
sum(`hit_hws`) as `hit_hws`,
sum(`damage`) as `damage`,
sum(`damage_real`) as `damage_real`
from `as_map_weapons`
group by `mode`, `uid`, `wid`, `tflag`;

-- ----------------------------
-- View structure for as_all_bodyhits
-- ----------------------------
DROP VIEW IF EXISTS `as_all_bodyhits`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER  VIEW `as_all_bodyhits` AS select
`mode`,
`uid`,
`wid`,
`tflag`,
sum(`generic`) as `generic`,
sum(`head`) as `head`,
sum(`chest`) as `chest`,
sum(`stomach`) as `stomach`,
sum(`leftarm`) as `leftarm`,
sum(`rightarm`) as `rightarm`,
sum(`leftleg`) as `leftleg`,
sum(`rightleg`) as `rightleg`
from `as_map_bodyhits`
group by `mode`, `uid`, `wid`, `tflag`;

-- ----------------------------
-- View structure for as_all_weapons_sum
-- ----------------------------
DROP VIEW IF EXISTS `as_all_weapons_sum`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER  VIEW `as_all_weapons_sum` AS select
`mode`,
`uid`,
`tflag`,
sum(`kill`) as `kill`,
sum(`kill_hs`) as `kill_hs`,
sum(`kill_ws`) as `kill_ws`,
sum(`kill_hws`) as `kill_hws`,
sum(`killed`) as `killed`,
sum(`killed_hs`) as `killed_hs`,
sum(`killed_ws`) as `killed_ws`,
sum(`killed_hws`) as `killed_hws`,
sum(`death`) as `death`,
sum(`shot`) as `shot`,
sum(`hit`) as `hit`,
sum(`hit_hs`) as `hit_hs`,
sum(`hit_ws`) as `hit_ws`,
sum(`hit_hws`) as `hit_hws`,
sum(`damage`) as `damage`,
sum(`damage_real`) as `damage_real`
from `as_map_weapons`
group by `mode`, `uid`, `tflag`;

-- ----------------------------
-- View structure for as_all_bodyhits_sum
-- ----------------------------
DROP VIEW IF EXISTS `as_all_bodyhits_sum`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER  VIEW `as_all_bodyhits_sum` AS select
`mode`,
`uid`,
`tflag`,
sum(`generic`) as `generic`,
sum(`head`) as `head`,
sum(`chest`) as `chest`,
sum(`stomach`) as `stomach`,
sum(`leftarm`) as `leftarm`,
sum(`rightarm`) as `rightarm`,
sum(`leftleg`) as `leftleg`,
sum(`rightleg`) as `rightleg`
from `as_map_bodyhits`
group by `mode`, `uid`, `tflag`;

-- ----------------------------
-- View structure for as_all_stats
-- ----------------------------
DROP VIEW IF EXISTS `as_all_stats`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER  VIEW `as_all_stats` AS select
`r`.`mode`,
`r`.`uid`,
`b`.`planting`,
`b`.`planted`,
`b`.`explode`,
`b`.`defusing`,
`b`.`defused`,
`r`.`unassigned`,
`r`.`t`,
`r`.`ct`,
`r`.`spectator`,
`r`.`win_t`,
`r`.`win_ct`,
`r`.`win_shares`,
`r`.`first_kill`,
`r`.`first_death`,
`r`.`last_kill`,
`r`.`last_death`,
`r`.`mvp`,
`r`.`kast`,
`r`.`assist`,
`r`.`assist_by_damage`,
`r`.`assist_by_flashbang`,
`r`.`kill_1`,
`r`.`kill_2`,
`r`.`kill_3`,
`r`.`kill_4`,
`r`.`kill_5`,
`r`.`1_vs_1`,
`r`.`1_vs_2`,
`r`.`1_vs_3`,
`r`.`1_vs_4`,
`r`.`1_vs_5`,
`r`.`time`,
`w`.`kill`,
`w`.`kill_hs`,
`w`.`kill_ws`,
`w`.`kill_hws`,
`w`.`killed`,
`w`.`killed_hs`,
`w`.`killed_ws`,
`w`.`killed_hws`,
`w`.`death`,
`w`.`shot`,
`w`.`hit`,
`w`.`hit_hs`,
`w`.`hit_ws`,
`w`.`hit_hws`,
`w`.`damage`,
`w`.`damage_real`
from `as_all_bombs` as `b` right join `as_all_rounds` as `r` on `r`.`mode` = `b`.`mode` and `r`.`uid` = `b`.`uid` inner join `as_all_weapons_sum` as `w` on `w`.`tflag` = '0' and `w`.`mode` = `r`.`mode` and `w`.`uid` = `r`.`uid`;
