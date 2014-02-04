VDIFN
=====

UW-Madison Department of Plant Pathology's Vegetable Disease and Insect Forecasting Network.

### wgrib2

See [compiling instructions](http://www.cpc.ncep.noaa.gov/products/wesley/wgrib2/compile_questions.html).

1. Install gcc & gfortran: `sudo apt-get install gcc gfortran`.
2. [Download wgrib2](http://www.ftp.cpc.ncep.noaa.gov/wd51we/wgrib2/wgrib2.tgz).
3. Extract: `tar -zvxf wgrib2.tgz`.
4. cd: `cd grib2`.
5. Set the C compiler: `export CC=gcc`
6. Set the fortran compiler: `export FC=gfortran`.
7. Compile: `make`
