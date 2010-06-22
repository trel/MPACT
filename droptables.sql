-- use this with "mysql -uusername -h -p mpact < droptables.sql"

DROP TABLE advisorships;
DROP TABLE committeeships;
DROP TABLE disciplines;
DROP TABLE dissertations;
DROP TABLE glossary;
DROP TABLE logs;
DROP TABLE names;
DROP TABLE people;
DROP TABLE profs_at_dept;
DROP TABLE schools;
DROP TABLE urls;
DROP TABLE users;

-- then load data with "mysql -uusername -h -p mpact < datadump.sql"
