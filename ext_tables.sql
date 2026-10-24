CREATE TABLE tx_maijobs_application (
    first_name varchar(255) DEFAULT '' NOT NULL,
    last_name varchar(255) DEFAULT '' NOT NULL,
    email varchar(255) DEFAULT '' NOT NULL,
    message text,
    cv int(11) unsigned DEFAULT '0' NOT NULL,
    status varchar(20) DEFAULT 'pending' NOT NULL,
    submitted_at int(11) unsigned DEFAULT '0' NOT NULL,
    job int(11) unsigned DEFAULT '0' NOT NULL
);

# Job doktype (122) metadata on pages — People BGM-33
CREATE TABLE pages (
    tx_maijobs_description text,
    tx_maijobs_requirements text,
    tx_maijobs_deadline int(11) unsigned DEFAULT '0' NOT NULL,
    tx_maijobs_status varchar(20) DEFAULT 'open' NOT NULL,
    tx_maijobs_legacy_job_uid int(11) DEFAULT 0 NOT NULL
);
