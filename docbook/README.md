# MantisBT Documentation

This directory contains sources for the Mantis Documentation.

We are using *Docbook XML* to write manuals, and
[Publican](https://fedorahosted.org/publican/) to manage the build
in the final formats (PDF, HTML, etc).

Please refer to our [Wiki](https://mantisbt.org/wiki/doku.php/mantisbt:docbook)
for instructions on how to setup the required tools and
further details on how to build the manuals.


## Building

Build the documentation with:

```
cd Admin_Guide
make
```

Or, executing Publican manually

```
cd Admin_Guide
publican build --formats=pdf,html --langs=en-US
```
