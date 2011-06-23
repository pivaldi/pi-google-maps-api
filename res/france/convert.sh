#!/bin/bash

[ -e "contour.kml" ] && {
    echo "Entre dans $(pwd)"
    sed "s/\(.*\)<coordinates>.*/\1<coordinates>/g;" contour.kml > "contour-simple.kml"

    cat >contour.asy<<EOF
    pair[] p={(
EOF
            sed "s/.*<coordinates>\(.*\)<\/coordinates>.*/\1/g;s/,0 /),(/g;s/,0//g" contour.kml >> contour.asy
            cat >>contour.asy<<EOF
            )};

    pair[] pp;
    pp.push(p[0]);
    for (int i=1; i < p.length; ++i) {
        if(abs(p[i]-pp[pp.length-1]) > 0.6*10e-2) {
                pp.push(p[i]);
            }
    }

    string s;
    for (int i=0; i < pp.length; ++i) {
        s += string(xpart(pp[i]))+','+string(ypart(pp[i]))+',0 ';
    }

    write(s);
    //write(pp.length);

    //draw(unitcircle);
EOF

    asy contour.asy > tmp
    cat tmp >> "contour-simple.kml"

    cat >>"contour-simple.kml"<<EOF
    </coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark></Document></kml>
EOF

    sed -i "s/id=\"\(.*\)\">/id=\"\1-simple\">/;N;s/\n//" "contour-simple.kml"

    cat >"contour-simple.php"<<EOF
<?php
\$coords=array(
EOF
    sed 's/ \([-0123456789,\.]*\),0/,array(\1)/g;s/^\(.*\),0,/array(\1),/' tmp >> "contour-simple.php"

    cat >>"contour-simple.php"<<EOF
);
?>
EOF

sed -i "N;N;s/(\n/(/g" "contour-simple.php"
sed -i "N;N;s/ \n//g;" "contour-simple.php"

sed -i 's/(\(-*[0-9\.]*\),\(-*[0-9\.]*\))/(\2,\1)/g' "contour-simple.php"

}
echo "Sort de $(pwd)"
echo


# Local variables:
# coding: utf-8
# End:

