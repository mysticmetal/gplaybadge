echo js
uglifyjs web/static/life.js -o web/static/life.min.js --stats -v -c

echo html
htmlmin -o res/views/error.min.twig res/views/error.twig    
#htmlmin -o res/views/analytics.min.twig res/views/analytics.twig 
htmlmin -o res/views/home.min.twig res/views/home.twig

echo css
cleancss -d --s0 -o web/static/style.min.css web/static/style.css
