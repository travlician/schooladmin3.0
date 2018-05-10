mv schooladminconstants.php schooladminconstants.cfg
rename .php .addon Addon_*.php
rm -f *.php
rm -f *.css
rm -f *.html
rm -f *.js
rm -f *.otf
rm -f *.sql
rm -f *.class
rm -f *.w_js
rm -f *.diff
rm -f *.eot
rm -f *.ttf
rm -f *.woff
rm -f *.sh
rm -f schooladmin.jpg
rm -f schooladminbg.jpg
rm -f headerlogo.png
rm -f jwrapfront.png
rm -f textlogo.gif
rm -f logo.png
rm -f auashield.gif
rm -f emptyshield.gif
rm -f logoSKOA.jpg
rm -f captcha
rm -f inputlib
rm -f displayelements
rm -f PNG
rm -f dbupgrades
ln ../codebase3.0/* .
ln ../codebase3.0/addon_aruba/* .
ln ../codebase3.0/addon_aruba_bo/* .
ln -s ../codebase3.0/captcha .
ln -s ../codebase3.0/inputlib
ln -s ../codebase3.0/displayelements
ln -s ../codebase3.0/PNG
ln -s ../codebase3.0/dbupgrades
rename .addon .php Addon_*.addon
for f in Addon_*
do 
 rm -f $f
 ln ../codebase3.0/addon_students/$f .
done
mv schooladminconstants.cfg schooladminconstants.php