If you use Professional Edition of dhtmlxGrid, you'd better copy Pro codebase into dhtmlxGrid folder
as currently it cotains Standard Editoon files and some used functionality is not availabe there.


These are the steps and actions we did and showed in video: 


0_init

html:
1. include grid files (js and css)
2. create DIV container for grid
3. initialize grid with two editable columns: Column A (initial width   100px) and Column B (take remaining place). Skin "modern"


1_load

php:
4. created connector.php file and write code there
5. inserted grid_connector.php
6. added DB connection string
7. initialized Grid COnnector based on database connection
8. used render_table method with grid50 table.
9. used item_id as rows identifier
10 fields item_nm and item_cd to populate grid

html:
11. load grid data from connector.php: loadXML


3_load50000

html:
12. added grid extension for big datasets: dhtmlxgrid_srnd.js
13. enabled Smart Rendering mode

php:
14.in php file added support for dynamical loading (this is last modification in php file)
15. table changed to grid50000 (same fields but much more data - about 50k records)


4_paging

html:
15. added css file necessary for skin for paging (named bricks)
16. srnd extension replaced with pgn (paging)
17. added area for paging information (will use its ID a bit later)
18. replaced Smart Rendering mode with paging
19. set paging skin to "bricks" (here above ID was used)

5_filter_sort

html:
20. added filter extension file
21. added connector js file
22. created filter fields in grid header of type #connector_text_filter, which means filtering will be server side processed by connector (value contains mask)
23. set sorting type to "connector", which means it will be server side sorting processed by connector


6_savedata

html:
24. added dataprocessor library file
25. created instance of Dataprocessor and linked it with grid and connector.php file.



CONCLUSION: this is basic functionality of dhtmlxConnector. Using provided server side API and clinet side API of dhtmlxDataProcessor you can make it work exactly like you need and it will still remain "Easy to use".




