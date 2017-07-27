CREATE TABLE IF NOT EXISTS `shellfind` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fingerprint` text NOT NULL,
  `file_path` text NOT NULL,
  `last_scan_date` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

