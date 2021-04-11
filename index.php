
<?php
	if ( !defined( 'FPDM_PATH' ) ) {                
	    define( 'FPDM_PATH', dirname(__FILE__).'/' );
	}
    // Load FPDM Plugin
    require(FPDM_PATH. 'fpdm.php');

    // generate fdf from json file
    function generate_fdf($pdf_file_source, $json_file_source) {
        $file_name = explode('.', $pdf_file_source);
        // get content from json file
        $json_content = file_get_contents($json_file_source);

        // generate associative array from json content
        $values=json_decode($json_content, true);
        // Generate FDF file from associative array and pdf  
        $fdf = "%FDF-1.2\n1 0 obj\n";
        $fdf .= "1 0 obj \n<< /FDF ";
        $fdf .= "<< /Fields [\n";
    
        foreach ($values as $key => $val)
            $fdf .= "<< /V ($val)/T ($key) >> \n";
        
        $fdf .= "]\n/F ($pdf_file_source) >>";
        $fdf .= ">>\nendobj\ntrailer\n<<\n";
        $fdf .= "/Root 1 0 R \n\n>>\n";
        $fdf .= "%%EOF";
        
        $fp = fopen($file_name[0].".fdf", "w");
        fwrite($fp, $fdf);
        fclose($fp);
    }    
    // Merge fdf into original PDF file
    function generate_pdf_form($pdf_file_source, $json_file_source, $output_file_name) {
        // generate fdf file from pdf_file_source and json.
        generate_fdf($pdf_file_source, $json_file_source);
        // get content from json file
        $json_content = file_get_contents($json_file_source);

        // generate associative array from json content
        $values=json_decode($json_content, true);
        // get path of original pdf
        $output_file_path = explode('.', $pdf_file_source);
        // determine the output file
        global $output_file;
        $output_file=dirname($output_file_path[0])."/".$output_file_name.".pdf";
        // print_r($output_file);
        $pdf = new FPDM($pdf_file_source);  
        // $pdf->Flatten();
        $pdf->Load($values, false); // second parameter: false if field values are in ISO-8859-1, true if UTF-8
        $pdf->Merge();	
        $pdf->Output('F', $output_file);
        // exec("pdftocairo -pdf $output_file $output_file");
    }

    $pdf_file_source="pdf/probate-court-cover-letter.pdf";
    $output_file_name="output_file_name";
    $json_file_source="sample.json";

    // this following function is what you want. 
    
    generate_pdf_form($pdf_file_source, $json_file_source, $output_file_name);
    echo '<script type="text/javascript">
	            window.location = "'.$output_file.'"
	       </script>';
    // echo "Success!!!!";
?>