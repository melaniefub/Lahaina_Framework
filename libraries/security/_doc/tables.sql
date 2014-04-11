--
-- Table structure for table `role`
--
CREATE TABLE `role` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8 NOT NULL,
  `role_type` varchar(45) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_type_UNIQUE` (`role_type`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='Role table of security library';

--
-- Dumping data for table `role`
--

INSERT INTO `role` VALUES 
	(1, 'Administrator', 'ADMIN'),
	(2, 'Verwalter', 'MANAGER');

--
-- Table structure for table `user`
--
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(45) CHARACTER SET utf8 NOT NULL,
  `email` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `fk_role_id_idx` (`user_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `FK_user_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COMMENT='User table of security library';

--
-- Dumping data for table `user`
--

INSERT INTO `user` VALUES 
	(1,'admin','admin@lahaina.ch','7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 1),
	(2,'manager','manager@lahaina.ch','7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 2);
