-- Do NOT include CREATE USER or CREATE DATABASE here. 
-- The environment variables in your YAML already created them.

CREATE TABLE person (
    id SERIAL PRIMARY KEY,
    first_name VARCHAR(255),
    surname VARCHAR(255),
    age INTEGER
);

CREATE TABLE item (
    id SERIAL PRIMARY KEY,
    label VARCHAR(255)
);

CREATE TABLE allocation (
    person_id INTEGER REFERENCES person(id),
    item_id INTEGER REFERENCES item(id),
    quantity INTEGER,
    PRIMARY KEY (person_id, item_id)
);

-- Ensure the admin user has rights to these new tables
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO admin;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO admin;
