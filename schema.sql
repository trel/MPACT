
# Dump of table advisorships
# ------------------------------------------------------------

CREATE TABLE `advisorships` (
  `dissertation_id` int(11) NOT NULL default '0',
  `person_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`dissertation_id`,`person_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table committeeships
# ------------------------------------------------------------

CREATE TABLE `committeeships` (
  `dissertation_id` int(11) NOT NULL default '0',
  `person_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`dissertation_id`,`person_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table disciplines
# ------------------------------------------------------------

CREATE TABLE `disciplines` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) default NULL,
  `description` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table dissertations
# ------------------------------------------------------------

CREATE TABLE `dissertations` (
  `id` int(11) NOT NULL auto_increment,
  `person_id` int(11) default NULL,
  `completedyear` int(4) default NULL,
  `school_id` int(11) default NULL,
  `title` text NOT NULL,
  `abstract` text NOT NULL,
  `status` int(11) NOT NULL default '0',
  `notes` text NOT NULL,
  `discipline_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table glossary
# ------------------------------------------------------------

CREATE TABLE `glossary` (
  `id` int(11) NOT NULL auto_increment,
  `term` varchar(255) default NULL,
  `definition` text,
  PRIMARY KEY  (`id`),
  KEY `term` (`term`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table logs
# ------------------------------------------------------------

CREATE TABLE `logs` (
  `id` int(11) NOT NULL auto_increment,
  `logged_at` datetime default NULL,
  `user` varchar(255) default NULL,
  `ip` varchar(255) default NULL,
  `type` varchar(255) NOT NULL default 'general',
  `message` text,
  `action` varchar(255) default NULL,
  `agent` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table names
# ------------------------------------------------------------

CREATE TABLE `names` (
  `id` int(11) NOT NULL auto_increment,
  `person_id` int(11) default NULL,
  `firstname` varchar(100) default NULL,
  `middlename` varchar(100) default NULL,
  `lastname` varchar(100) default NULL,
  `suffix` varchar(20) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table people
# ------------------------------------------------------------

CREATE TABLE `people` (
  `id` int(11) NOT NULL auto_increment,
  `preferred_name_id` int(11) default NULL,
  `degree` varchar(255) NOT NULL default '',
  `a_score` int(11) default NULL,
  `c_score` int(11) default NULL,
  `ac_score` int(11) default NULL,
  `g_score` int(11) default NULL,
  `w_score` int(11) default NULL,
  `t_score` int(11) default NULL,
  `ta_score` int(11) default NULL,
  `td_score` float default NULL,
  `fmi_score` float default NULL,
  `fme_score` float default NULL,
  `scores_calculated` datetime default NULL,
  `regenerate_dotgraph` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table profs_at_dept
# ------------------------------------------------------------

CREATE TABLE `profs_at_dept` (
  `school_id` int(11) default NULL,
  `discipline_id` int(11) default NULL,
  `professors` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table schools
# ------------------------------------------------------------

CREATE TABLE `schools` (
  `id` int(11) NOT NULL auto_increment,
  `shortname` varchar(100) default NULL,
  `fullname` varchar(255) default NULL,
  `city` varchar(100) default NULL,
  `state` varchar(100) default NULL,
  `country` varchar(100) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table urls
# ------------------------------------------------------------

CREATE TABLE `urls` (
  `id` int(11) NOT NULL auto_increment,
  `url` text,
  `description` text,
  `updated_at` datetime default NULL,
  `person_id` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table users
# ------------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL default '',
  `password` varchar(255) default NULL,
  `fullname` varchar(255) default NULL,
  `email` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

