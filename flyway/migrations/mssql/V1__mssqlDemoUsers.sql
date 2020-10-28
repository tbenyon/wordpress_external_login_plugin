CREATE TABLE TEST_USER_TABLE (
  UserID int,
  FirstName varchar(20),
  LastName varchar(20),
  NickName varchar(20),
  CreatedDate date,
  DOB date,
  UserType varchar(20),
  LastUpdate datetime,
  ContactNumber varchar(20),
  Hash varchar(128),
  cms_state varchar(128) DEFAULT '0',
  Email varchar(128),
  address varchar(128),
  city varchar(128),
  zip_code varchar(128),
  state varchar(128),
  phone varchar(128),
);


INSERT INTO TEST_USER_TABLE (UserID, FirstName, LastName, NickName, CreatedDate, DOB, UserType, LastUpdate, ContactNumber, Hash, cms_state, Email, address, city, zip_code, state, phone)
VALUES
	(2,'Thomas','Benyon','tom','2016-09-10','1987-04-23','Mentor','2018-03-24 11:27:36','07792736282','$2y$12$6tDeJO9VmCNOJjGKuLnDyugdCuBv9LAGZxJaLAmgC/19KKCy3bq7u','blocked','tom.benyon2@gmail.com',NULL,NULL,NULL,NULL,NULL),
	(1,'Will','Benyon','monkeyMan','2016-09-10','1982-10-20','Mentor','2016-09-10 10:16:52','07236388181','NULL',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
