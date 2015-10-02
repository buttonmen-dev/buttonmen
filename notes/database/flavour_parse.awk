# Note that I have preprocessed all embedded double quotes to the
# character sequence ~~, so that awk can correctly separate the
# CSV into fields
BEGIN {
  FPAT = "([^,]+)|(\"([^\"]+)\")"
}

{
  gsub("[[:cntrl:]]", "");
  if ($4 != "") {
    print "UPDATE button SET flavor_text=" $4 " WHERE name=\"" $2 "\";";
  }
}

