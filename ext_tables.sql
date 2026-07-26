CREATE TABLE tx_maijobs_job (
    title varchar(255) DEFAULT '' NOT NULL,
    description text,
    requirements text,
    deadline int(11) unsigned DEFAULT '0' NOT NULL,
    status varchar(20) DEFAULT 'open' NOT NULL,
    categories int(11) unsigned DEFAULT '0' NOT NULL,
    slug varchar(2048) NOT NULL DEFAULT ''
);

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
