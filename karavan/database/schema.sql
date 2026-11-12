-- Karavan — schéma de base de données (compatible SQLite ; voir README pour MySQL)

DROP TABLE IF EXISTS housing_bookings;
DROP TABLE IF EXISTS ride_bookings;
DROP TABLE IF EXISTS housings;
DROP TABLE IF EXISTS rides;
DROP TABLE IF EXISTS festivals;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        VARCHAR(80)  NOT NULL,
    email       VARCHAR(160) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    city        VARCHAR(80),
    bio         TEXT,
    role        VARCHAR(20) NOT NULL DEFAULT 'member',   -- member | admin
    suspended   BOOLEAN NOT NULL DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE festivals (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    slug        VARCHAR(120) NOT NULL UNIQUE,
    name        VARCHAR(120) NOT NULL,
    city        VARCHAR(80)  NOT NULL,
    region      VARCHAR(80)  NOT NULL,
    venue       VARCHAR(160),
    start_date  DATE NOT NULL,
    end_date    DATE NOT NULL,
    genres      VARCHAR(160) NOT NULL,          -- liste séparée par des virgules
    tagline     VARCHAR(200),
    description TEXT,
    lineup      TEXT,                           -- liste séparée par des virgules
    attendance  INTEGER,
    price_from  DECIMAL(8,2),
    hue         INTEGER DEFAULT 20,             -- teinte de la couverture générée
    featured    BOOLEAN DEFAULT 0
);

CREATE TABLE rides (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id        INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    festival_id    INTEGER NOT NULL REFERENCES festivals(id) ON DELETE CASCADE,
    departure_city VARCHAR(80) NOT NULL,
    meeting_point  VARCHAR(160),
    departure_at   DATETIME NOT NULL,
    return_at      DATETIME,
    seats          INTEGER NOT NULL,
    price          DECIMAL(8,2) NOT NULL,
    vehicle        VARCHAR(80),
    notes          TEXT,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE housings (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id      INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    festival_id  INTEGER NOT NULL REFERENCES festivals(id) ON DELETE CASCADE,
    title        VARCHAR(140) NOT NULL,
    type         VARCHAR(20)  NOT NULL,          -- tente | van | chambre | appartement | maison
    city         VARCHAR(80)  NOT NULL,
    distance_km  DECIMAL(5,1) NOT NULL,
    capacity     INTEGER NOT NULL,
    price_night  DECIMAL(8,2) NOT NULL,
    description  TEXT,
    amenities    VARCHAR(255),                   -- liste séparée par des virgules
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ride_bookings (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    ride_id    INTEGER NOT NULL REFERENCES rides(id) ON DELETE CASCADE,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    seats      INTEGER NOT NULL DEFAULT 1,
    message    TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE housing_bookings (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    housing_id INTEGER NOT NULL REFERENCES housings(id) ON DELETE CASCADE,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    guests     INTEGER NOT NULL DEFAULT 1,
    nights     INTEGER NOT NULL DEFAULT 1,
    message    TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_rides_festival    ON rides(festival_id);
CREATE INDEX idx_housings_festival ON housings(festival_id);
