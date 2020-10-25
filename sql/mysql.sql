# phpMyAdmin MySQL-Dump
# version 2.2.6
# http://phpwizard.net/phpMyAdmin/
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Erstellungszeit: 07. November 2003 um 16:10
# Server Version: 4.00.01
# PHP-Version: 4.3.1
# Datenbank : `xoops`
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `xcgal_albums`
#

CREATE TABLE xcgal_albums (
    aid           INT(11)           NOT NULL AUTO_INCREMENT,
    title         VARCHAR(255)      NOT NULL DEFAULT '',
    description   TEXT              NOT NULL,
    visibility    INT(11)           NOT NULL DEFAULT '0',
    uploads       ENUM ('YES','NO') NOT NULL DEFAULT 'NO',
    comments      ENUM ('YES','NO') NOT NULL DEFAULT 'YES',
    votes         ENUM ('YES','NO') NOT NULL DEFAULT 'YES',
    pos           INT(11)           NOT NULL DEFAULT '0',
    category      INT(11)           NOT NULL DEFAULT '0',
    pic_count     INT(11)           NOT NULL DEFAULT '0',
    thumb         INT(11)           NOT NULL DEFAULT '0',
    last_addition DATETIME          NOT NULL DEFAULT '0000-00-00 00:00:00',
    stat_uptodate ENUM ('YES','NO') NOT NULL DEFAULT 'NO',
    PRIMARY KEY (aid),
    KEY alb_category (category)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `xcgal_categories`
#

CREATE TABLE xcgal_categories (
    cid           INT(11)           NOT NULL AUTO_INCREMENT,
    owner_id      INT(11)           NOT NULL DEFAULT '0',
    name          VARCHAR(255)      NOT NULL DEFAULT '',
    description   TEXT              NOT NULL,
    pos           INT(11)           NOT NULL DEFAULT '0',
    parent        INT(11)           NOT NULL DEFAULT '0',
    subcat_count  INT(11)           NOT NULL DEFAULT '0',
    alb_count     INT(11)           NOT NULL DEFAULT '0',
    pic_count     INT(11)           NOT NULL DEFAULT '0',
    stat_uptodate ENUM ('YES','NO') NOT NULL DEFAULT 'NO',
    PRIMARY KEY (cid),
    KEY cat_parent (parent),
    KEY cat_pos (pos),
    KEY cat_owner_id (owner_id)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `xcgal_ecard`
#

CREATE TABLE xcgal_ecard (
    e_id            VARCHAR(25)  NOT NULL DEFAULT '0',
    sess_id         VARCHAR(32)  NOT NULL DEFAULT '',
    sender_ip       VARCHAR(15)  NOT NULL DEFAULT '',
    sender_uid      MEDIUMINT(8) NOT NULL DEFAULT '0',
    sender_name     VARCHAR(60)  NOT NULL DEFAULT '',
    sender_email    VARCHAR(60)  NOT NULL DEFAULT '',
    recipient_name  VARCHAR(60)  NOT NULL DEFAULT '',
    recipient_email VARCHAR(60)  NOT NULL DEFAULT '',
    greetings       VARCHAR(250) NOT NULL DEFAULT '',
    message         TEXT         NOT NULL,
    s_time          INT(10)      NOT NULL DEFAULT '0',
    pid             INT(11)      NOT NULL DEFAULT '0',
    picked          TINYINT(1)   NOT NULL DEFAULT '0',
    PRIMARY KEY (e_id)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `xcgal_pictures`
#

CREATE TABLE xcgal_pictures (
    pid            INT(11)           NOT NULL AUTO_INCREMENT,
    aid            INT(11)           NOT NULL DEFAULT '0',
    filepath       VARCHAR(255)      NOT NULL DEFAULT '',
    filename       VARCHAR(255)      NOT NULL DEFAULT '',
    filesize       INT(11)           NOT NULL DEFAULT '0',
    total_filesize INT(11)           NOT NULL DEFAULT '0',
    pwidth         SMALLINT(6)       NOT NULL DEFAULT '0',
    pheight        SMALLINT(6)       NOT NULL DEFAULT '0',
    hits           INT(10)           NOT NULL DEFAULT '0',
    mtime          INT(11)           NOT NULL DEFAULT '0',
    ctime          INT(11)           NOT NULL DEFAULT '0',
    owner_id       INT(11)           NOT NULL DEFAULT '0',
    owner_name     VARCHAR(40)       NOT NULL DEFAULT '',
    pic_rating     INT(11)           NOT NULL DEFAULT '0',
    votes          INT(11)           NOT NULL DEFAULT '0',
    title          VARCHAR(255)      NOT NULL DEFAULT '',
    caption        TEXT              NOT NULL,
    keywords       VARCHAR(255)      NOT NULL DEFAULT '',
    approved       ENUM ('YES','NO') NOT NULL DEFAULT 'NO',
    user1          VARCHAR(255)      NOT NULL DEFAULT '',
    user2          VARCHAR(255)      NOT NULL DEFAULT '',
    user3          VARCHAR(255)      NOT NULL DEFAULT '',
    user4          VARCHAR(255)      NOT NULL DEFAULT '',
    url_prefix     TINYINT(4)        NOT NULL DEFAULT '0',
    randpos        INT(11)           NOT NULL DEFAULT '0',
    ip             VARCHAR(15)       NOT NULL DEFAULT '',
    sent_card      INT(10)           NOT NULL DEFAULT '0',
    PRIMARY KEY (pid),
    KEY pic_hits (hits),
    KEY pic_rate (pic_rating),
    KEY aid_approved (aid, approved),
    KEY randpos (randpos),
    KEY pic_aid (aid)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `xcgal_usergroups`
#

CREATE TABLE xcgal_usergroups (
    group_id               INT(11)      NOT NULL AUTO_INCREMENT,
    group_name             VARCHAR(255) NOT NULL DEFAULT '',
    group_quota            INT(11)      NOT NULL DEFAULT '0',
    has_admin_access       TINYINT(4)   NOT NULL DEFAULT '0',
    can_rate_pictures      TINYINT(4)   NOT NULL DEFAULT '0',
    can_send_ecards        TINYINT(4)   NOT NULL DEFAULT '0',
    can_post_comments      TINYINT(4)   NOT NULL DEFAULT '0',
    can_upload_pictures    TINYINT(4)   NOT NULL DEFAULT '0',
    can_create_albums      TINYINT(4)   NOT NULL DEFAULT '0',
    pub_upl_need_approval  TINYINT(4)   NOT NULL DEFAULT '1',
    priv_upl_need_approval TINYINT(4)   NOT NULL DEFAULT '1',
    xgroupid               SMALLINT(5)  NOT NULL DEFAULT '0',
    PRIMARY KEY (group_id)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `xcgal_votes`
#

CREATE TABLE xcgal_votes (
    pic_id    MEDIUMINT(9) NOT NULL DEFAULT '0',
    ip        VARCHAR(60)  NOT NULL DEFAULT '',
    vote_time INT(11)      NOT NULL DEFAULT '0',
    v_uid     INT(11)      NOT NULL DEFAULT '0',
    PRIMARY KEY (pic_id, ip)
)
    ENGINE = ISAM;

INSERT INTO xcgal_categories
VALUES (1, 0, 'User galleries', 'This category contains albums that belong to Coppermine users.', 1, 0, 0, 0, 0, 'NO');
