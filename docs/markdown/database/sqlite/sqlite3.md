# Ark with SQLite3

Ark supports SQLite3 by extended the class `SQLite3` as `ArkSqlite3`,
providing extra methods for SQLite3 usage, as following.

1. execute a query and get the count of affected rows
1. run insert query and get the last inserted ID
1. safely execute query with quoted parameters
1. safely query with quoted parameters, to fetch a matrix, a row, a column or one value.

You may refer to the test sample codes for details.