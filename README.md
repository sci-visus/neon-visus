# neon-visus

Interactive visualization service for NSF [NEON data portal](https://neonscience.org) based on the [OpenVisus framework](https://github.com/sci-visus/OpenVisus). 

This repository contains:
* neonapi/ - web API to discover datasets available for interactive streaming 
* db/ - database files with metadata used by the neonapi
* visus/ - OpenVisusJS submodule containing the web viewer and JS streaming interface
* index.html - webpage for testing neonapi and streaming service
* visus-frame.html - configurable component that can be embedded in existing webpage for interactive streaming

This repository is deployed at this URL: https://neon.visus.org/

## neonapi

The neonapi implements two calls:
* products/ - return a JSON list of data products IDs
* products/[dataproduct_id] - given a data procut ID returns a JSON response with the following information:
  * productCode
  * siteCodes
    * siteCode - 4 chars site code
    * availableMonths - month and year available for this site
    * availableDataUrls - configuration string for web viewer to stream the selected _availableMonth_

## visus-frame iframe parameters

The [visus-frame.html](https://github.com/sci-visus/neon-visus/blob/master/visus-frame.html) file contains a webviewer that can be used as iframe. This iframe handles two parameters:
* title - the title string that will be pu in the header of the viewer
* dataset - the configuration string for the webviewer (e.g., pointers to the streaming server and default transfer function settings)
