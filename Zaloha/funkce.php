<?php
    
function draggItems($xml) {
     $dragArr = "";
     $y=0;
     foreach($xml->xpath('//Format') as $format)  {
     $dragArr[$y] = $format->Name;
     $y++; 
                  }
     return $dragArr;
     }
function process($pravidlaXML, $metaXML) {
      $text = "";
      $spojka = "";
      foreach ($pravidlaXML->AssociationRule as $ar) {
              
               foreach ($ar->Antecedent as $ante) {
                     $text .= "IF \n";
                  foreach($ante->Cedent as $cedent) {
                     $text .= processCedent($metaXML, $cedent);
                      
                  }
               }
                $text .= "\n";
                $text .= "THEN \n";
                foreach($ar->Consequent as $conse) {
                   foreach($conse->Cedent as $cedent) {
                       $text .= processCedent($metaXML, $cedent);
                   }
                }                  
               }
                return $text;
}

/**
 * @param $metaXML
 * @param $cedent
 * @param $text
 * @return array
 */
function processCedent($metaXML, $cedent)
{
    $textCed = "";
    $attrs = $cedent->attributes();
    if ($attrs["connective"] == "Disjunction") {
        $spojka = "or";
    } else if ($attrs["connective"] == "Conjunction") {
        $spojka = "and";
    } else {
        $spojka = "";
    }
   // echo $cedent->asXML();
    foreach($cedent->Cedent as $cedChild) {
        $textCed .= processCedent($metaXML, $cedChild);
        $jePar = 1;
        $textCed .= " $spojka ";
   //     return $textCed;
    }
    if($cedent->Cedent) {
        $textCed = substr($textCed, 0, -(strlen($spojka)+2));
    }
      
     if($jePar != 1) {
    if ($spojka == "") {
        $textCed .= "not(";
        }
        foreach ($cedent->Attribute as $attr) {
            $attAttr = $attr->attributes();
            $attrAttr = $attAttr["format"];
            foreach ($metaXML->xpath('//Format') as $format) {
                $attFormat = $format->attributes();
                $formatAttr = $attFormat["id"];
                if (!strcmp($attrAttr, $formatAttr)) {
                    $attributeName = $format->Name;
                    $textCed .= '"'. $attributeName .'" ';
                }
            }
            foreach ($attr->Category as $cat) {
                $attCat = $cat->attributes();
                $catUri = $attCat["id"];
                // echo $attrAttr;
                $pomocPrubeh = 0;
                foreach ($metaXML->xpath("//Format[@id = '$attrAttr']//NominalBin") as $bin) {
                    $attBin = $bin->attributes();
                    $binUri = $attBin["id"];
                    if ((string)$binUri == (string)$catUri) {
                        $textCed .= processBin($bin);
                        $pomocPrubeh = 1;break;
                    }
                }
                if($pomocPrubeh != 1) {
                foreach ($metaXML->xpath("//Format[@id = '$attrAttr']//IntervalBin") as $bin) {
                    $attBin = $bin->attributes();
                    $binUri = $attBin["id"];
                    if ((string)$binUri == (string)$catUri) {
                        $textCed .= processBin($bin);

                    }
            }
                }}
            $textCed = substr($textCed, 0, -3);
            $textCed .= " $spojka ";


        }
        $lengthS = strlen($spojka) + 2;
        $textCed = substr($textCed, 0, -$lengthS);
        }
        if ($spojka == "") {
          $textCed .= ")";
        }
        return $textCed;
    }
 //   return $textCed;



/**
 * @param $bin
 * @param $text
 * @return array
 */
function processBin($bin)
{
    if (isset($bin->Value)) {

        $attributeValue = $bin->Value;
        $textBin = "equals $attributeValue or";
        return $textBin;
    } else {
        $interval = $bin->Interval->attributes();
        $spodniKraj = $interval["leftMargin"];
        $horniKraj = $interval["rightMargin"];
        switch ($interval["closure"]) {
            case "closedClosed":
                $textBin = "equals or is higher than " . $spodniKraj . " and equals or is lower than " . $horniKraj . " or ";
                break;
            case "openClosed":
                $textBin = "is higher than " . $spodniKraj . " and equals or is lower than " . $horniKraj . " or ";
                break;
            case "closedOpen":
                $textBin = "equals or is higher than " . $spodniKraj . " and is lower than " . $horniKraj . " or ";
                break;
            case "openOpen":
                $textBin = "is higher than " . $spodniKraj . " and is lower than " . $horniKraj . " or ";
        }
                return $textBin;

        }
    }


?>