The MantisBT Entity-Relationship Diagram
========================================

The diagram was built using MySQL Workbench [1] version 5.2.40. The MantisBT
schema was reverse-engineered based on a freshly installed database, then the
relationships between tables and corresponding cardinalities were manually
added.

[1] http://dev.mysql.com/downloads/tools/workbench/


Editing Recommendations
-----------------------

* Make sure that the tables are big enough to display all columns
* Position the tables to minimize the number of intersections between the
  relationships lines (not always easy as the software does not offer much
  flexibility for positioning the connectors).
* Update the MantisBT and Schema version numbers as appropriate in the
  'Title' note (top-left corner) of the diagram
* Do not forget to bump the revision number
* Save the file


Exporting
---------

To save the diagram in a more widely readable format:

  * Start MySQL Workbench and open mantisbt.mwb
  * Go to File / Export
  * Select Export as PNG, SVG or Single Page PDF

Recommended naming convention for exported files:

    mantisbt_VVV_SSS_erd_rR.XXX

where

  * VVV is the MantisBT version (e.g. 1.2)
  * SSS is the corresponding schema version (e.g. 183)
  * R indicates the diagram's revision number
  * XXX is the file's extension (e.g. pdf, png)


Updating the Documentation
--------------------------

To keep the Developers Guide up-to-date as the ERD is modified:

1. Export the diagram as PNG
2. Save the file in /docbook/Developers_Guide/en-US/images/erd.png
3. Build the docbook and check that the updated file is there
4. Commit changes

Also remember to update the PDF on http://mantisbt.org/docs/erd

1. Export the diagram as single-file PDF
2. Save the file to a temp location as per above naming convention
3. Upload the file to the server
4. Remove the old file if necessary
