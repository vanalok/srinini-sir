-- Srinivasulu IFS — engagement + analytics schema
-- Import in cPanel → phpMyAdmin (select your DB first), or:  mysql DB < schema.sql

CREATE TABLE IF NOT EXISTS engagement (
  slug   VARCHAR(255) NOT NULL PRIMARY KEY,
  views  INT UNSIGNED NOT NULL DEFAULT 0,
  likes  INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS likes_by_visitor (
  slug       VARCHAR(255) NOT NULL,
  visitor    CHAR(40)     NOT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (slug, visitor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS comments (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug       VARCHAR(255) NOT NULL,
  name       VARCHAR(120) NOT NULL,
  body       TEXT         NOT NULL,
  status     ENUM('pending','approved','spam') NOT NULL DEFAULT 'pending',
  visitor    CHAR(40)     DEFAULT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (slug), INDEX (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS visits (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  path       VARCHAR(255) NOT NULL,
  visitor    CHAR(40)     NOT NULL,
  referrer   VARCHAR(255) DEFAULT NULL,
  ua         VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (created_at), INDEX (path), INDEX (visitor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
