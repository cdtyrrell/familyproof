-- CREATE DATABASE familyproof;

-- USE familyproof;

CREATE TABLE sources (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    category VARCHAR(100),
    citation TEXT NOT NULL,
    sourcedate VARCHAR(10),
    provenance ENUM('original', 'derived', 'authored', 'unknown') NOT NULL DEFAULT 'unknown',
    informants VARCHAR(255),
    mediaurl TEXT,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    lastmodified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE individuals (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    presumedname VARCHAR(255) NOT NULL,
    presumedsex ENUM('male', 'female', 'non-binary', 'unknown') NOT NULL DEFAULT 'unknown',
    presumeddates VARCHAR(100),
    identifier TEXT GENERATED ALWAYS AS (CONCAT(presumedname, '[', 
        CASE
            WHEN presumedsex = 'male' THEN "M"
            WHEN presumedsex = 'female' THEN "F"
            WHEN presumedsex = 'non-binary' THEN "X"
            ELSE "?"
        END,
    ']', '(', presumeddates, ')')),
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    lastmodified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE questions (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    question VARCHAR(255) NOT NULL,
    questiontype ENUM('vital','biographical','relationship','descriptive')
);
/*
vital        : date, place
biographical : date, place, text
relationship : date, place, person
descriptive  : text
alt: 
    identity     : when, where (, what)
    relationship : (when, where,) who
    activity     : what
*/

CREATE TABLE information (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    sourceid INT NOT NULL,
    locationwithinsource VARCHAR(100),
    subjectid INT NOT NULL,
    questionid INT NOT NULL,
    content TEXT NOT NULL,
    context ENUM('primary', 'secondary', 'indeterminable', 'unknown') NOT NULL DEFAULT 'unknown',
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    lastmodified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sourceid) REFERENCES sources (id),
    FOREIGN KEY (subjectid) REFERENCES subjects (id),
    FOREIGN KEY (questionid) REFERENCES questions (id)
);

CREATE TABLE assertions (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    assertionstatus ENUM('needs-review','analyzed') DEFAULT 'needs-review',
    subjectid INT NOT NULL,
    questionid INT NOT NULL,
    conclusion TEXT,
    relatedsubjectid INT,
    dateoccurred VARCHAR(100),
    place TEXT,
    analysis TEXT,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    lastmodified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subjectid) REFERENCES subjects (id),
    FOREIGN KEY (relatedsubjectid) REFERENCES subjects (id),
    FOREIGN KEY (questionid) REFERENCES questions (id)
);

CREATE TABLE evidence (
    informationid INT NOT NULL,
    assertionid INT NOT NULL,
    assessment TEXT,
    quality ENUM('direct', 'indirect', 'negative', 'unknown') NOT NULL DEFAULT 'unknown',
    applicability ENUM('applicable','not-applicable','unclaimed') DEFAULT 'unclaimed',
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    lastmodified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY(informationid, assertionid),
    FOREIGN KEY (informationid) REFERENCES information (id),
    FOREIGN KEY (assertionid) REFERENCES assertions (id)
);

/* CREATE TABLE relationships (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    subject1id INT NOT NULL,
    subject2id INT NOT NULL,
    relation ENUM('spouse', 'child') NOT NULL,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    lastmodified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subject1id) REFERENCES subjects (id),
    FOREIGN KEY (subject2id) REFERENCES subjects (id)
); */

/* CREATE TABLE relationshipsupport (
    relationshipid INT NOT NULL,
    assertionid INT NOT NULL,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    lastmodified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY(relationshipid, assertionid),
    FOREIGN KEY (relationshipid) REFERENCES relationships (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (assertionid) REFERENCES assertions (id) ON DELETE RESTRICT ON UPDATE CASCADE
); */

CREATE TABLE researchlog (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    researchdate DATE DEFAULT (CURRENT_DATE),
    assertionid INT NOT NULL,
    repository TEXT,
    searchparams TEXT,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    lastmodified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assertionid) REFERENCES assertions (id)
);

CREATE TABLE researchlogentries (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    researchlogid INT NOT NULL,
    sourceid INT NOT NULL,
    FOREIGN KEY (researchlogid) REFERENCES researchlog (id),
    FOREIGN KEY (sourceid) REFERENCES sources (id)
);

CREATE TABLE sourcetemplates (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    category VARCHAR(100) NOT NULL,
    pagecitation TEXT NOT NULL,
    linecitation VARCHAR(100),
    headings TEXT
);

CREATE TABLE logins (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255),
    hashcredentials CHAR(60);
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    lastmodified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE projects (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    title TEXT,
    userid INT,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    lastmodified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (userid) REFERENCES logins (id)
);

/* LOAD DEFAULT DATA */
INSERT INTO questions (question, questiontype) VALUES ('Name/Known As','descriptive'),('Residence','vital'),('Birth','vital'),('Marriage','relationship'),('Death','vital'),('Sex','descriptive'),
('Burial','biographical'),('Baptism','biographical'),('Child of','relationship'),('Adoption','relationship'),('Divorce','relationship'),('Engagement','relationship'),('Degree','biographical'),('Will','biographical'),('Probate','biographical'),('Religion','biographical'),('Deed','biographical'),
('Naturalization','biographical'),('Immigration','biographical'),('Cause of Death','biographical'),('Occupation','biographical'),('Retirement','biographical');
INSERT INTO sourcetemplates (category, pagecitation, linecitation) VALUES ('CENS: U.S. Federal, 1790','1790 U.S. census, COUNTY County, STATE, LOCALITY, p. # (penned), NARA microfilm publication X#, roll #.','line #, NAME'),('CENS: U.S. Federal, 1800','1800 U.S. census, COUNTY County, STATE, LOCALITY, p. # (penned), NARA microfilm publication X#, roll #.','line #, NAME'),('CENS: U.S. Federal, 1810','1810 U.S. census, COUNTY County, STATE, LOCALITY, p. # (penned), NARA microfilm publication X#, roll #.','line #, NAME'),('CENS: U.S. Federal, 1820','1820 U.S. census, COUNTY County, STATE, LOCALITY, p. # (penned), NARA microfilm publication X#, roll #.','line #, NAME'),('CENS: U.S. Federal, 1830','1830 U.S. census, COUNTY County, STATE, LOCALITY, p. # (penned), NARA microfilm publication X#, roll #.','line #, NAME'),('CENS: U.S. Federal, 1840','1840 U.S. census, COUNTY County, STATE, LOCALITY, p. # (penned), NARA microfilm publication X#, roll #.','line #, NAME'),('CENS: U.S. Federal, 1850','1850 U.S. census, COUNTY County, STATE, population schedule, LOCALITY, p. # (stamped), NARA microfilm publication X#, roll #.','dwelling #, family #, SURNAME'),('CENS: U.S. Federal, 1860','1860 U.S. census, COUNTY County, STATE, population schedule, LOCALITY, p. # (stamped), NARA microfilm publication X#, roll #.','dwelling #, family #, SURNAME'),('CENS: U.S. Federal, 1870','1870 U.S. census, COUNTY County, STATE, population schedule, LOCALITY, p. # (stamped), NARA microfilm publication X#, roll #.','dwelling #, family #, SURNAME'),('CENS: U.S. Federal, 1880','1880 U.S. census, COUNTY County, STATE, population schedule, LOCALITIES, folio # (stamped), enumeration district (ER) #, sheet #-X, NARA microfilm publication X#, roll #.','dwelling #, family #, NAME'),('CENS: U.S. Federal, 1890','1890 U.S. census, COUNTY County, STATE, population schedule, LOCALITIES, folio # (stamped), enumeration district (ER) #, sheet #-X, NARA microfilm publication X#, roll #.','dwelling #, family #, NAME'),('CENS: U.S. Federal, 1900','1900 U.S. census, COUNTY County, STATE, population schedule, LOCALITIES, folio # (stamped), enumeration district (ER) #, sheet #-X, NARA microfilm publication X#, roll #.','dwelling #, family #, NAME'),('CENS: U.S. Federal, 1910','1910 U.S. census, COUNTY County, STATE, population schedule, LOCALITIES, folio # (stamped), enumeration district (ER) #, sheet #-X, NARA microfilm publication X#, roll #.','dwelling #, family #, NAME'),('CENS: U.S. Federal, 1920','1920 U.S. census, COUNTY County, STATE, population schedule, LOCALITIES, folio # (stamped), enumeration district (ER) #, sheet #-X, NARA microfilm publication X#, roll #.','dwelling #, family #, NAME'),('CENS: U.S. Federal, 1930','1930 U.S. census, COUNTY County, STATE, population schedule, LOCALITIES, folio # (stamped), enumeration district (ER) #, sheet #-X, NARA microfilm publication X#, roll #.','dwelling #, family #, NAME'),('CENS: U.S. Federal, 1940','1940 U.S. census, COUNTY County, STATE, population schedule, LOCALITIES, folio # (stamped), enumeration district (ER) #, sheet #-X, NARA microfilm publication X#, roll #.','dwelling #, family #, NAME'),('CENS: U.S. Federal, 1950','1950 U.S. census, COUNTY County, STATE, population schedule, LOCALITIES, folio # (stamped), enumeration district (ER) #, sheet #-X, NARA microfilm publication X#, roll #.','dwelling #, family #, NAME');
INSERT INTO sourcetemplates (category, pagecitation, linecitation) VALUES ('CENS: U.S. State, 1875','1875 STATE state census, COUNTY County, population schedule, LOCALITY, p. #, REPOSITORY, REPOLOCATION, microfilm #.','dwelling #, family #, NAME'),('CENS: U.S. State, 1885','1885 STATE state census, COUNTY County, population schedule, LOCALITY, p. #, REPOSITORY, REPOLOCATION, microfilm #.','dwelling #, family #, NAME'),('CENS: U.S. State, 1895','1895 STATE state census, COUNTY County, population schedule, LOCALITY, p. #, REPOSITORY, REPOLOCATION, microfilm #.','dwelling #, family #, NAME'),('CENS: U.S. State, 1905','1905 STATE state census, COUNTY County, population schedule, LOCALITY, p. #, REPOSITORY, REPOLOCATION, microfilm #.','dwelling #, family #, NAME'),('CENS: U.S. State, 1915','1915 STATE state census, COUNTY County, population schedule, LOCALITY, p. #, REPOSITORY, REPOLOCATION, microfilm #.','dwelling #, family #, NAME'),('CENS: U.S. State, 1925','1925 STATE state census, COUNTY County, population schedule, LOCALITY, p. #, REPOSITORY, REPOLOCATION, microfilm #.','dwelling #, family #, NAME'),('CENS: U.S. State, 1935','1935 STATE state census, COUNTY County, population schedule, LOCALITY, p. #, REPOSITORY, REPOLOCATION, microfilm #.','dwelling #, family #, NAME'),('CENS: U.S. State, 1945','1945 STATE state census, COUNTY County, population schedule, LOCALITY, p. #, REPOSITORY, REPOLOCATION, microfilm #.','dwelling #, family #, NAME'),('CENS: U.S. State, 1955','1955 STATE state census, COUNTY County, population schedule, LOCALITY, p. #, REPOSITORY, REPOLOCATION, microfilm #.','dwelling #, family #, NAME');
