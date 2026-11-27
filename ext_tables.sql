#
# Table structure for table 'tx_yellowpages2_domain_model_company'
#
# maps2 13 does not define the column type of "maps2Relation" fields anymore.
# Keep the integer column, which was created by the Maps2Registry of maps2 12.
#
CREATE TABLE tx_yellowpages2_domain_model_company (
	tx_maps2_uid int(11) unsigned DEFAULT '0' NOT NULL
);
