VDIFN
=====

UW-Madison Department of Plant Pathology's Vegetable Disease and Insect Forecasting Network.

### wgrib2

#### Installation

See [compiling instructions](http://www.cpc.ncep.noaa.gov/products/wesley/wgrib2/compile_questions.html).

1. Install gcc & gfortran: `sudo apt-get install gcc gfortran`.
2. [Download wgrib2](http://www.ftp.cpc.ncep.noaa.gov/wd51we/wgrib2/wgrib2.tgz).
3. Extract: `tar -zvxf wgrib2.tgz`.
4. cd: `cd grib2`.
5. Set the C compiler: `export CC=gcc`
6. Set the fortran compiler: `export FC=gfortran`.
7. Compile: `make`

#### Usage

See [common options](http://www.cpc.ncep.noaa.gov/products/wesley/wgrib2/short_cmd_list.html).

1. Get inventory listing: `wgrib2 nam.t00z.awip1200.tm00.grib2`
2. Use dd to make sub files of what you want (given byte start and length which can be calculated using the inventory listing): `dd if=nam.t00z.awip1200.tm00.grib2 ibs=1 skip=934102 count=97535 of=snippet.grib2`
3. Use -[undefine](http://www.cpc.ncep.noaa.gov/products/wesley/wgrib2/undefine.html) to specify the grid constraints.
4. Use -[csv](http://www.cpc.ncep.noaa.gov/products/wesley/wgrib2/csv.html) for easy parsing in PHP.
