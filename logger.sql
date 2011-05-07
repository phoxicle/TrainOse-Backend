CREATE TABLE com_pheide_trainose (
	uid int(11) auto_increment,
	tstamp DATETIME,
	severity int(11) unsigned DEFAULT '0' NOT NULL,
	url text,
	data text,
	source varchar(255), 
	destination varchar(255), 
	num_routes int(11) unsigned DEFAULT '0' NOT NULL,
	cached tinyint(4) unsigned DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid)
);
