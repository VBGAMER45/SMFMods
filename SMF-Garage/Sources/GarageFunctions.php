<?php
/**********************************************************************************
 * GarageFunctions.php                                                             *
 ***********************************************************************************
 * SMF Garage: Simple Machines Forum Garage (MOD)                                  *
 * =============================================================================== *
 * Software Version:           SMF Garage 3.0.0                                    *
 * Install for:                2.0.9-2.0.99, 2.1.0-2.1.99                         *
 * Original Developer:         RRasco (http://www.smfgarage.com)                   *
 * Copyright 2026 by:          vbgamer45 (https://www.smfhacks.com)               *
 * Copyright 2015 by:          Bruno Alves (margarett.pt@gmail.com                 *
 * Copyright 2007-2011 by:     SMF Garage (http://www.smfgarage.com)               *
 *                             RRasco (rrasco@smfgarage.com)                       *
 * phpBB Garage by:            Esmond Poynton (esmond.poynton@gmail.com)           *
 ***********************************************************************************
 * See the "SMF_Garage_License.txt" file for details.                              *
 *              http://www.opensource.org/licenses/BSD-3-Clause                    *
 **********************************************************************************/

// Generate make options
function make_select($make_id = 0)
{

    global $smcFunc;

    $make_select = '';
    $result = $smcFunc['db_query']('', '
        SELECT * 
        FROM {db_prefix}garage_makes 
        ORDER BY make ASC',
        array(// no values
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($result)) {
        $make_select .= ($make_id == $row['id']) ? '<option value="' . $row['id'] . '" selected="selected">' . $smcFunc['htmlspecialchars']($row['make']) . '</option>' : '<option value="' . $row['id'] . '">' . $smcFunc['htmlspecialchars']($row['make']) . '</option>';
    }
    return $make_select;
}

// Generate model options
function model_options($form_name)
{

    global $smcFunc;

    $model_options = "<script type=\"text/javascript\">
    var dol = new DynamicOptionList();
    dol.setFormName(\"" . $form_name . "\");
    dol.addDependentFields(\"make_id\",\"model_id\");
    dol.forValue(\"\").addOptionsTextValue(\"Select Make First\", \"\");
    ";

    $result = $smcFunc['db_query']('', '
        SELECT *
        FROM {db_prefix}garage_models
        ORDER BY model ASC',
        array(// no values
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($result)) {
        $model_options .= "dol.forValue(\"" . $row['make_id'] . "\").addOptionsTextValue(\"" . addslashes($smcFunc['htmlspecialchars']($row['model'])) . "\", \"" . $row['id'] . "\");\n    ";
    }
    $model_options .= "</script>";
    return $model_options;
}

// Generate year range
function year_options($start = 1980, $offset = 1, $current = 0)
{
    global $txt;

    // This is gonna be needed...
    loadLanguage('Garage');

    $start_year = $start;
    $current_year = date('Y');
    $end_year = $current_year + $offset;
    $year_options = "<option value=\"\" selected=\"selected\">" . $txt['smfg_select_year'] . "</option>\n        ";
    for ($i = $start_year; $i <= $end_year; $i++) {
        if ($current == $i) {
            $year_options .= "<option value=\"" . $i . "\" selected=\"selected\">" . $i . "</option>\n        ";
        } else {
            $year_options .= "<option value=\"" . $i . "\">" . $i . "</option>\n        ";
        }
    }
    return $year_options;
}

// Generate mod category options
function cat_select($cat_id = 0)
{

    global $smcFunc;

    $cat_select = '';
    $result = $smcFunc['db_query']('', '
        SELECT * 
        FROM {db_prefix}garage_categories 
        ORDER BY field_order ASC',
        array(// no values
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($result)) {
        $cat_select .= ($cat_id == $row['id']) ? '<option value="' . $row['id'] . '" selected="selected">' . $smcFunc['htmlspecialchars']($row['title']) . '</option>' : '<option value="' . $row['id'] . '">' . $smcFunc['htmlspecialchars']($row['title']) . '</option>';
    }
    return $cat_select;
}

// Generate manufacturer options
function manufacturer_select($manufacturer_id = 0)
{

    global $smcFunc;

    $manufacturer_select = '';
    $result = $smcFunc['db_query']('', '
        SELECT * 
        FROM {db_prefix}garage_business 
        WHERE product = 1
        ORDER BY title ASC',
        array(// no values
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($result)) {
        $manufacturer_select .= ($manufacturer_id == $row['id']) ? '<option value="' . $row['id'] . '" selected="selected">' . $smcFunc['htmlspecialchars']($row['title']) . '</option>' : '<option value="' . $row['id'] . '">' . $smcFunc['htmlspecialchars']($row['title']) . '</option>';
    }
    return $manufacturer_select;
}

// Generate product options
// This function generates the dol js for all possible product select options
function product_options($form_name)
{
    global $smcFunc;

    $product_options = "<script type=\"text/javascript\">
    var dol2 = new DynamicOptionList();
    dol2.setFormName(\"" . $form_name . "\");
    dol2.addDependentFields(\"category_id\", \"manufacturer_id\", \"product_id\");
    dol2.forValue(\"\").addOptionsTextValue(\"Select Category First\", \"\");\n    ";

    $request = $smcFunc['db_query']('', '
        SELECT DISTINCT {db_prefix}garage_products.category_id
        FROM {db_prefix}garage_products, {db_prefix}garage_business 
        WHERE {db_prefix}garage_products.business_id = {db_prefix}garage_business.id',
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($man_opts[$count]['category_id']) = $row;
        $product_options .= "dol2.forValue(\"" . $man_opts[$count]['category_id'] . "\").addOptionsTextValue(";
        $request2 = $smcFunc['db_query']('', '
            SELECT DISTINCT {db_prefix}garage_business.title, {db_prefix}garage_products.business_id
            FROM {db_prefix}garage_products, {db_prefix}garage_business 
            WHERE {db_prefix}garage_products.business_id = {db_prefix}garage_business.id 
                AND {int:category_id} = {db_prefix}garage_products.category_id
                ORDER BY {db_prefix}garage_business.title ASC',
            array(
                'category_id' => $man_opts[$count]['category_id'],
            )
        );
        $count2 = 0;
        while ($row2 = $smcFunc['db_fetch_row']($request2)) {
            list($man_opts[$count2]['title'],
                $man_opts[$count2]['business_id']) = $row2;
            if ($count2 > 0) {
                $separator = ", ";
            } else {
                $separator = "";
            }
            $product_options .= $separator . "\"" . addslashes($smcFunc['htmlspecialchars']($man_opts[$count2]['title'])) . "\", \"" . $man_opts[$count2]['business_id'] . "\"";
            $count2++;
        }
        $smcFunc['db_free_result'] ($request2);
        $product_options .= ");\n    ";

        $request2 = $smcFunc['db_query']('', '
            SELECT DISTINCT {db_prefix}garage_products.business_id
            FROM {db_prefix}garage_products, {db_prefix}garage_business 
            WHERE {db_prefix}garage_products.business_id = {db_prefix}garage_business.id 
                AND {int:category_id} = {db_prefix}garage_products.category_id',
            array(
                'category_id' => $man_opts[$count]['category_id'],
            )
        );
        $count3 = 0;
        $product_options .= "dol2.forValue(\"\").forValue(\"\").addOptionsTextValue(\"Select Manufacturer First\", \"\");\n";
        while ($row2 = $smcFunc['db_fetch_row']($request2)) {
            list($man_opts[$count3]['business_id']) = $row2;
            $product_options .= "dol2.forValue(\"" . $man_opts[$count]['category_id'] . "\").forValue(\"" . $man_opts[$count3]['business_id'] . "\").addOptionsTextValue(";
            $request3 = $smcFunc['db_query']('', '
                SELECT DISTINCT id, title
                FROM {db_prefix}garage_products
                WHERE category_id = {int:category_id}
                    AND business_id = {int:business_id}
                    ORDER BY title ASC',
                array(
                    'category_id' => $man_opts[$count]['category_id'],
                    'business_id' => $man_opts[$count3]['business_id'],
                )
            );
            $count4 = 0;
            while ($row3 = $smcFunc['db_fetch_row']($request3)) {
                list($prod_opts[$count4]['id'],
                    $prod_opts[$count4]['title']) = $row3;
                $prod_opts[$count4]['title'] = addslashes($smcFunc['htmlspecialchars']($prod_opts[$count4]['title']));
                if ($count4 > 0) {
                    $separator = ", ";
                } else {
                    $separator = "";
                }
                $product_options .= $separator . "\"" . $prod_opts[$count4]['title'] . "\", \"" . $prod_opts[$count4]['id'] . "\"";
                $count4++;
            }
            $smcFunc['db_free_result'] ($request3);
            $product_options .= ");\n    ";
            $count3++;
        }
        $smcFunc['db_free_result'] ($request2);
        $count++;
    }
    $smcFunc['db_free_result'] ($request);
    $product_options .= "</script>";
    return $product_options;
}

// Generate shop options
function shop_select($shop_id = 0)
{

    global $smcFunc;

    $shop_select = '';
    $result = $smcFunc['db_query']('', '
        SELECT * 
        FROM {db_prefix}garage_business 
        WHERE retail = 1
        ORDER BY title ASC',
        array(// no values
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($result)) {
        $shop_select .= ($shop_id == $row['id']) ? '<option value="' . $row['id'] . '" selected="selected">' . $smcFunc['htmlspecialchars']($row['title']) . '</option>' : '<option value="' . $row['id'] . '">' . $smcFunc['htmlspecialchars']($row['title']) . '</option>';
    }
    return $shop_select;
}

// Generate install/garage options
function install_select($install_id = 0)
{

    global $smcFunc;

    $install_select = '';
    $result = $smcFunc['db_query']('', '
        SELECT * 
        FROM {db_prefix}garage_business 
        WHERE garage = 1
        ORDER BY title ASC',
        array(// no values
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($result)) {
        $install_select .= ($install_id == $row['id']) ? '<option value="' . $row['id'] . '" selected="selected">' . $smcFunc['htmlspecialchars']($row['title']) . '</option>' : '<option value="' . $row['id'] . '">' . $smcFunc['htmlspecialchars']($row['title']) . '</option>';
    }
    return $install_select;
}

// Generate insurer options
function insurer_select($insurer_id = 0)
{

    global $smcFunc;

    $insurer_select = '';
    $result = $smcFunc['db_query']('', '
        SELECT * 
        FROM {db_prefix}garage_business 
        WHERE insurance = 1
        ORDER BY title ASC',
        array(// no values
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($result)) {
        $insurer_select .= ($insurer_id == $row['id']) ? '<option value="' . $row['id'] . '" selected="selected">' . $smcFunc['htmlspecialchars']($row['title']) . '</option>' : '<option value="' . $row['id'] . '">' . $smcFunc['htmlspecialchars']($row['title']) . '</option>';
    }
    return $insurer_select;
}

// Generate dynocenter options
function dynocenter_select($dynocenter_id = 0)
{

    global $smcFunc;

    $dynocenter_select = '';
    $result = $smcFunc['db_query']('', '
        SELECT * 
        FROM {db_prefix}garage_business 
        WHERE dynocenter = 1
        ORDER BY title ASC',
        array(// no values
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($result)) {
        $dynocenter_select .= ($dynocenter_id == $row['id']) ? '<option value="' . $row['id'] . '" selected="selected">' . $smcFunc['htmlspecialchars']($row['title']) . '</option>' : '<option value="' . $row['id'] . '">' . $smcFunc['htmlspecialchars']($row['title']) . '</option>';
    }
    return $dynocenter_select;
}

// Generate track options
function track_select($track_id = 0, $disable_pending = false)
{

    global $smcFunc;

    $track_select = '';
    $result = $smcFunc['db_query']('', '
        SELECT id, title 
        FROM {db_prefix}garage_tracks 
        ' . (($disable_pending) ? ' WHERE pending != "1" ' : '') . '
        ORDER BY title ASC',
        array(// no values
        )
    );
    $count = 0;
    while ($row[$count] = $smcFunc['db_fetch_assoc']($result)) {
        $track_select .= ($track_id == $row[$count]['id']) ? '<option value="' . $row[$count]['id'] . '" selected="selected">' . $smcFunc['htmlspecialchars']($row[$count]['title']) . '</option>' : '<option value="' . $row[$count]['id'] . '">' . $smcFunc['htmlspecialchars']($row[$count]['title']) . '</option>';
        $count++;
    }
    return $track_select;
}

// Generate service type options
function service_type_select($type_id = 0)
{

    global $smcFunc;

    $service_type_select = '';
    $result = $smcFunc['db_query']('', '
        SELECT id, title 
        FROM {db_prefix}garage_service_types
        ORDER BY field_order ASC',
        array(// no values
        )
    );
    $count = 0;
    while ($row[$count] = $smcFunc['db_fetch_assoc']($result)) {
        $service_type_select .= ($type_id == $row[$count]['id']) ? '<option value="' . $row[$count]['id'] . '" selected="selected">' . $smcFunc['htmlspecialchars']($row[$count]['title']) . '</option>' : '<option value="' . $row[$count]['id'] . '">' . $smcFunc['htmlspecialchars']($row[$count]['title']) . '</option>';
        $count++;
    }
    return $service_type_select;
}

// Generate currency options
function currency_select($currency_id = 0)
{

    global $smcFunc;

    $currency_select = '';
    $result = $smcFunc['db_query']('', '
        SELECT id, title 
        FROM {db_prefix}garage_currency
        ORDER BY field_order ASC',
        array(// no values
        )
    );
    $count = 0;
    while ($row[$count] = $smcFunc['db_fetch_assoc']($result)) {
        $currency_select .= ($currency_id == $row[$count]['id']) ? '<option value="' . $row[$count]['id'] . '" selected="selected">' . $smcFunc['htmlspecialchars']($row[$count]['title']) . '</option>' : '<option value="' . $row[$count]['id'] . '">' . $smcFunc['htmlspecialchars']($row[$count]['title']) . '</option>';
        $count++;
    }
    return $currency_select;
}

// Generate engine type options
function engine_type_select($type_id = 0)
{

    global $smcFunc;

    $engine_type_select = '';
    $result = $smcFunc['db_query']('', '
        SELECT id, title 
        FROM {db_prefix}garage_engine_types
        ORDER BY field_order ASC',
        array(// no values
        )
    );
    $count = 0;
    while ($row[$count] = $smcFunc['db_fetch_assoc']($result)) {
        $engine_type_select .= ($type_id == $row[$count]['id']) ? '<option value="' . $row[$count]['id'] . '" selected="selected">' . $smcFunc['htmlspecialchars']($row[$count]['title']) . '</option>' : '<option value="' . $row[$count]['id'] . '">' . $smcFunc['htmlspecialchars']($row[$count]['title']) . '</option>';
        $count++;
    }
    return $engine_type_select;
}

// Generate premium type options
function premium_type_select($type_id = 0)
{

    global $smcFunc;

    $premium_type_select = '';
    $result = $smcFunc['db_query']('', '
        SELECT id, title 
        FROM {db_prefix}garage_premium_types
        ORDER BY field_order ASC',
        array(// no values
        )
    );
    $count = 0;
    while ($row[$count] = $smcFunc['db_fetch_assoc']($result)) {
        $premium_type_select .= ($type_id == $row[$count]['id']) ? '<option value="' . $row[$count]['id'] . '" selected="selected">' . $smcFunc['htmlspecialchars']($row[$count]['title']) . '</option>' : '<option value="' . $row[$count]['id'] . '">' . $smcFunc['htmlspecialchars']($row[$count]['title']) . '</option>';
        $count++;
    }
    return $premium_type_select;
}

// Generate lap type options
function lap_type_select($type_id = 0)
{

    global $smcFunc;

    $lap_type_select = '';
    $result = $smcFunc['db_query']('', '
        SELECT id, title 
        FROM {db_prefix}garage_lap_types
        ORDER BY field_order ASC',
        array(// no values
        )
    );
    $count = 0;
    while ($row[$count] = $smcFunc['db_fetch_assoc']($result)) {
        $lap_type_select .= ($type_id == $row[$count]['id']) ? '<option value="' . $row[$count]['id'] . '" selected="selected">' . $smcFunc['htmlspecialchars']($row[$count]['title']) . '</option>' : '<option value="' . $row[$count]['id'] . '">' . $smcFunc['htmlspecialchars']($row[$count]['title']) . '</option>';
        $count++;
    }
    return $lap_type_select;
}

// Generate track condition options
function track_condition_select($condition_id = 0)
{

    global $smcFunc;

    $track_condition_select = '';
    $result = $smcFunc['db_query']('', '
        SELECT id, title 
        FROM {db_prefix}garage_track_conditions
        ORDER BY field_order ASC',
        array(// no values
        )
    );
    $count = 0;
    while ($row[$count] = $smcFunc['db_fetch_assoc']($result)) {
        $track_condition_select .= ($condition_id == $row[$count]['id']) ? '<option value="' . $row[$count]['id'] . '" selected="selected">' . $smcFunc['htmlspecialchars']($row[$count]['title']) . '</option>' : '<option value="' . $row[$count]['id'] . '">' . $smcFunc['htmlspecialchars']($row[$count]['title']) . '</option>';
        $count++;
    }
    return $track_condition_select;
}

// Generate Dynorun options for linking to quartermile
function dynoqm_select($VID, $dynoqm_id = 0)
{

    global $smcFunc;

    $dynoqm_select = '';
    // Get available dynoruns for the vehicle
    $result = $smcFunc['db_query']('', '
        SELECT id, bhp, bhp_unit
        FROM {db_prefix}garage_dynoruns
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $VID,
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($result)) {
        $dynoqm_select .= ($dynoqm_id == $row['id']) ? '<option value="' . $row['id'] . '" selected="selected">' . $smcFunc['htmlspecialchars']($row['bhp']) . ' BHP @ ' . $smcFunc['htmlspecialchars']($row['bhp_unit']) . '</option>' : '<option value="' . $row['id'] . '">' . $smcFunc['htmlspecialchars']($row['bhp']) . ' BHP @ ' . $smcFunc['htmlspecialchars']($row['bhp_unit']) . '</option>';
    }
    return $dynoqm_select;
}

// Find the file extension for uploads
function findexts($filename)
{
    $filename = strtolower($filename);
    $exts = explode('.', $filename);

    $n = count($exts) - 1;
    $exts = $exts[$n];
    return $exts;
}

// generate thumbnails
function make_thumbnail($image, $store = 1, $remote = 0)
{
    global $smfgSettings, $ext, $boarddir;

    //echo "<br /><br />";
    //echo "<b>Image</b>: ".$image;
    //echo "<br />";

    $path = $boarddir . '/' . $smfgSettings['upload_directory'];
    $name = explode('.', $image);

    //echo "<b>Path</b>: ".$path;
    //echo "<br />";
    //echo "<b>Name</b>: ";
    //print_r( $name );
    //echo "<br />";

    $thumbname = $path . "cache/" . $name[0] . '_thumb.' . $ext;
    $newimage = $path . "cache/" . $image;

    //echo "<b>Thumbname</b>: ".$thumbname;
    //echo "<br />";
    //echo "<b>Newimage</b>: ".$newimage;
    //echo "<br />";


    if ($smfgSettings['enable_watermark']) {
        watermark_image($newimage, $path . $image);
    }

    if ($smfgSettings['enable_watermark_thumb'] == 2) {
        // if thumb is watermarked and resized, use watermared image to make
        // thumb
        $thumbfrom = $newimage;
        // If the image is remote
        if ($remote) {
            // Create the 'temp_' image
            copy($newimage, $path . 'temp_' . $image);
            watermark_image($path . 'temp_' . $image, $path . 'temp_' . $image);
            $thumbfrom = $path . 'temp_' . $image;
        }
    } else {
        // if thumb doesn't get resized watermark, or no watermark, use
        // original image
        $thumbfrom = $path . $image;
        // If the image is remote
        if ($remote) {
            // Create the 'temp_' image
            copy($newimage, $path . 'temp_' . $image);
            $thumbfrom = $path . 'temp_' . $image;
        }
    }

    //echo "<b>Thumbfrom</b>: ".$thumbfrom;
    //exit;

    // Make Thumbnail
    if ($smfgSettings['image_processor'] == 1) {
        make_thumbnail_im($thumbname, $thumbfrom);
    } else {
        if ($smfgSettings['image_processor'] == 2) {
            make_thumbnail_gd($thumbname, $thumbfrom);
        } else {
            make_thumbnail_none($thumbname, $thumbfrom);
        }
    }

    // If watermark thumb without a watermark resize
    if ($smfgSettings['enable_watermark_thumb'] == 1 && $smfgSettings['enable_watermark']) {
        watermark_image($thumbname, $thumbname);
    }

    // Copy if no watermarking or no image processor
    if (!$smfgSettings['image_processor'] || !$smfgSettings['enable_watermark']) {
        copy($path . $image, $newimage);
    }

    // if remote and not storing locally, just keep the thumb
    // also remove the temp_ file
    if ($remote && !$store) {
        unlink($path . $image);
        unlink($newimage);
        unlink($path . 'temp_' . $image);
    }
}

// Use Imagemagick to generate thumbnails
function make_thumbnail_im($thumbname, $image)
{
    global $smfgSettings;

    // Must come back and add auto paths here
    $imconvert = $smfgSettings['im_convert'];

    exec($imconvert . ' -quality 70 -geometry ' . $smfgSettings['thumbnail_resolution'] . ' ' . $image . ' ' . $thumbname);
}

// Use GD to generate thumbnails
// NOTE: ONLY Supports png, jpg and gif if available
function make_thumbnail_gd($thumbname, $image)
{
    global $smfgSettings;

    // Get Image Type
    $imagetype = get_image_type($image);

    // if not a support image type just return sliently instead of erroring
    if (!$imagetype) {
        return;
    }

    // Get new sizes
    list($width, $height) = getimagesize($image);
    if ($width > $height) {
        $divider = $width / $smfgSettings['thumbnail_resolution'];
        $newwidth = $smfgSettings['thumbnail_resolution'];
        $newheight = $height / $divider;
    } else {
        if ($height > $width) {
            $divider = $height / $smfgSettings['thumbnail_resolution'];
            $newwidth = $width / $divider;
            $newheight = $smfgSettings['thumbnail_resolution'];
        } else {
            $newwidth = $smfgSettings['thumbnail_resolution'];
            $newheight = $smfgSettings['thumbnail_resolution'];
        }
    }

    // Load original and create the new canvas
    $thumb = imagecreatetruecolor($newwidth, $newheight);
    // Put original file into a string then the format is auto detected
    $imagestring = file_get_contents($image);
    $source = imagecreatefromstring($imagestring);

    // Resize
    imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

    // Output
    if ($imagetype == "jpg") {
        imagejpeg($thumb, $thumbname);
    } else {
        if ($imagetype == "gif") {
            imagegif($thumb, $thumbname);
        } else {
            if ($imagetype == "png") {
                imagepng($thumb, $thumbname);
            }
        }
    }

    imagedestroy($thumb);
    imagedestroy($source);
}

// No Image Processor so make a copy to the new name
// NOTE: This might not work if ext isn't the same as the uploaded image
function make_thumbnail_none($thumbname, $image)
{
    copy($image, $thumbname);
}

// apply watermarks
function watermark_image($newimage, $image)
{
    global $smfgSettings, $boarddir;

    $path = $boarddir . '/';

    if ($smfgSettings['image_processor'] == 1) {
        watermark_image_im($path, $image, $newimage);
    } else {
        if ($smfgSettings['image_processor'] == 2) {
            watermark_image_gd($path, $image, $newimage);
        }
    }
}

// Use Imagemagick to apply watermarks
function watermark_image_im($path, $image, $newimage)
{
    global $smfgSettings;

    // Determine the watermark position
    switch ($smfgSettings['watermark_position']) {
        case 0:
            $position = "NorthWest";
            break;
        case 1:
            $position = "North";
            break;
        case 2:
            $position = "NorthEast";
            break;
        case 3:
            $position = "West";
            break;
        case 4:
            $position = "Center";
            break;
        case 5:
            $position = "East";
            break;
        case 6:
            $position = "SouthWest";
            break;
        case 7:
            $position = "South";
            break;
        case 8:
            $position = "SouthEast";
            break;
        default:
            $position = "SouthEast";
            break;
    }

    // Must come back and add auto paths here
    $imwatermark = $smfgSettings['im_composite'];

    exec($imwatermark . ' -dissolve ' . $smfgSettings['watermark_opacity'] . ' -gravity ' . $position . ' ' . $path . $smfgSettings['watermark_source'] . ' ' . $image . ' ' . $newimage);
}

// Use GD to apply watermarks
function watermark_image_gd($path, $image, $newimage)
{
    global $smfgSettings;

    // get image type
    $imagetype = get_image_type($image);

    // if not a support image type just return sliently instead of erroring
    if (!$imagetype) {
        return;
    }

    // Put watermark file into a string then the format is auto detected
    $watermarkstring = file_get_contents($path . $smfgSettings['watermark_source']);
    $watermarkres = imagecreatefromstring($watermarkstring);

    // get watermark size for location purposes
    $watermark_width = imagesx($watermarkres);
    $watermark_height = imagesy($watermarkres);

    // Load up image to be watermarked
    $newimageres = imagecreatetruecolor($watermark_width, $watermark_height);
    $imagestring = file_get_contents($image);
    $newimageres = imagecreatefromstring($imagestring);

    // Get the image size again for watermark location purposes
    $size = getimagesize($image);

    // Determine the watermark position
    switch ($smfgSettings['watermark_position']) {
        case 0:
            $dest_x = 5;
            $dest_y = 5;
            break;
        case 1:
            $dest_x = $size[0] - $watermark_width / 2;
            $dest_y = 5;
            break;
        case 2:
            $dest_x = $size[0] - $watermark_width - 5;
            $dest_y = 5;
            break;
        case 3:
            $dest_x = 5;
            $dest_y = $size[1] - $watermark_height / 2;
            $position = "West";
            break;
        case 4:
            $dest_x = $size[0] - $watermark_width / 2;
            $dest_y = $size[1] - $watermark_height / 2;
            $position = "Center";
            break;
        case 5:
            $dest_x = $size[0] - $watermark_width - 5;
            $dest_y = $size[1] - $watermark_height / 2;
            $position = "East";
            break;
        case 6:
            $dest_x = 5;
            $dest_y = $size[1] - $watermark_height - 5;
            break;
        case 7:
            $dest_x = $size[0] - $watermark_width / 2;
            $dest_y = $size[1] - $watermark_height - 5;
            $position = "South";
            break;
        case 8:
            $dest_x = $size[0] - $watermark_width - 5;
            $dest_y = $size[1] - $watermark_height - 5;
            break;
        default:
            $dest_x = $size[0] - $watermark_width - 5;
            $dest_y = $size[1] - $watermark_height - 5;
            break;
    }

    // Apply watermark
    imagecopymerge($newimageres, $watermarkres, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height,
        $smfgSettings['watermark_opacity']);

    // Output
    if ($imagetype == "jpg") {
        imagejpeg($newimageres, $newimage);
    } else {
        if ($imagetype == "gif") {
            imagegif($newimageres, $newimage);
        } else {
            if ($imagetype == "png") {
                imagepng($newimageres, $newimage);
            }
        }
    }

    imagedestroy($newimageres);
    imagedestroy($watermarkres);
}

// Return Image Type
function get_image_type($image)
{
    // Get current image type
    if (!function_exists('exif_imagetype')) {
        $imgDetails = getimagesize($image);
        switch ($imgDetails['mime']) {
            case 'image/gif':
                $imagetype = "gif";
                break;
            case 'image/jpg':
                $imagetype = "jpg";
                break;
            case 'image/png':
                $imagetype = "png";
                break;
            default:
                $imagetype = 0;
                break;
        }
    } else {
        switch (exif_imagetype($image)) {
            case IMAGETYPE_GIF:
                $imagetype = "gif";
                break;
            case IMAGETYPE_JPEG:
                $imagetype = "jpg";
                break;
            case IMAGETYPE_PNG:
                $imagetype = "png";
                break;
            default:
                $imagetype = 0;
                break;
        }
    }
    return $imagetype;
}

// Handle all image uploads
// handle_images($gallery, $source(0=local,1=remote), $file, $extra)
function handle_images($gallery, $source, $file, $extra = null)
{
    global $smfgSettings, $context, $ext, $smcFunc, $boarddir;

    if ($source == 0) {
        // Find the extension and generate filename and target directory
        $ext = findexts($file['name']);
    } else {
        if ($source == 1) {
            // Find the extension and generate filename and target directory
            $ext = findexts($file['url_image']);
        }
    }

    $ran = rand();
    $dir = $boarddir . '/' . $smfgSettings['upload_directory'];
    $attach_filename = $gallery . "_gallery_" . $ran . "." . $ext;
    $target = $dir . $attach_filename;
    $processed = $dir . "cache/" . $attach_filename;

    // Makesure the cache dir exists
    if (!is_dir($dir . "cache")) {
        mkdir($dir . "cache");
    }

    if ($source == 0) {
        // Move the uploaded file to uploads dir
        move_uploaded_file($file['tmp_name'], $target);

        // Generate the thumbnail and assign its attributes.
        make_thumbnail($attach_filename, 0);
        $attach_thumb_filename = $gallery . "_gallery_" . $ran . "_thumb." . $ext;
        $attach_thumb_dimensions = getimagesize($dir . "cache/" . $attach_thumb_filename);
        if (!$smfgSettings['image_processor']) {
            /* No way to make Thumbs
               Need to force the image size to be the max thumbsize
               And keep it proportionate */
            if ($attach_thumb_dimensions[0] > $attach_thumb_dimensions[1]) {
                $divider = $attach_thumb_dimensions[0] / $smfgSettings['thumbnail_resolution'];

                $attach_thumb_dimensions[0] = $smfgSettings['thumbnail_resolution'];
                $attach_thumb_dimensions[1] = $attach_thumb_dimensions[1] / $divider;
            } else {
                if ($attach_thumb_dimensions[1] > $attach_thumb_dimensions[0]) {
                    $divider = $attach_thumb_dimensions[1] / $smfgSettings['thumbnail_resolution'];

                    $attach_thumb_dimensions[1] = $smfgSettings['thumbnail_resolution'];
                    $attach_thumb_dimensions[0] = $attach_thumb_dimensions[0] / $divider;
                } else {
                    $attach_thumb_dimensions[0] = $smfgSettings['thumbnail_resolution'];
                    $attach_thumb_dimensions[1] = $smfgSettings['thumbnail_resolution'];;
                }
            }
        }
        $attach_thumb_width = $attach_thumb_dimensions[0];
        $attach_thumb_height = $attach_thumb_dimensions[1];

        // Determine if the file is an image
        $attach_dimensions = getimagesize($processed);
        if ($attach_dimensions[2] == 1 || 2 || 3 || 4) {
            $attach_is_image = 1;
        } else {
            $attach_is_image = 0;
        }

        // Get image filesizes
        $attach_filesize = filesize($processed);
        $attach_thumb_filesize = filesize($dir . "cache/" . $attach_thumb_filename);

        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_images',
            array(
                'vehicle_id' => 'int',
                'attach_location' => 'string',
                'attach_ext' => 'string',
                'attach_file' => 'string',
                'attach_thumb_location' => 'string',
                'attach_thumb_width' => 'int',
                'attach_thumb_height' => 'int',
                'attach_is_image' => 'int',
                'attach_date' => 'int',
                'attach_filesize' => 'int',
                'attach_thumb_filesize' => 'int',
                'attach_desc' => 'string',
            ),
            array(
                $context['vehicle_id'],
                $attach_filename,
                $ext,
                $file['name'],
                $attach_thumb_filename,
                $attach_thumb_width,
                $attach_thumb_height,
                $attach_is_image,
                $context['date_created'],
                $attach_filesize,
                $attach_thumb_filesize,
                $extra['attach_desc'],
            ),
            array(
                'vehicle_id'
            )
        );
        $context['image_id'] = $smcFunc['db_insert_id']($request);

        // If a remote image was supplied, use it instead
    } else {
        if ($source == 1) {
            // Go get the remote image, thumb it and store them
            // then gather some file attributes
            getRemoteImage($file['url_image'], $target);
            // Get image filesize before thumbing, or it will not exist by then!
            $attach_filesize = filesize($target);
            make_thumbnail($attach_filename, $smfgSettings['store_remote_images_locally'], $source);
            $attach_thumb_filename = $gallery . "_gallery_" . $ran . "_thumb." . $ext;
            // Get thumb filesize and dimensions
            $attach_thumb_filesize = filesize($dir . "cache/" . $attach_thumb_filename);
            $attach_thumb_dimensions = getimagesize($dir . "cache/" . $attach_thumb_filename);
            if (!$smfgSettings['image_processor']) {
                /* No way to make Thumbs
               Need to force the image size to be the max thumbsize
               And keep it proposionate */
                if ($attach_thumb_dimensions[0] > $attach_thumb_dimensions[1]) {
                    $divider = $attach_thumb_dimensions[0] / $smfgSettings['thumbnail_resolution'];

                    $attach_thumb_dimensions[0] = $smfgSettings['thumbnail_resolution'];
                    $attach_thumb_dimensions[1] = $attach_thumb_dimensions[1] / $divider;
                } else {
                    if ($attach_thumb_dimensions[1] > $attach_thumb_dimensions[0]) {
                        $divider = $attach_thumb_dimensions[1] / $smfgSettings['thumbnail_resolution'];

                        $attach_thumb_dimensions[1] = $smfgSettings['thumbnail_resolution'];
                        $attach_thumb_dimensions[0] = $attach_thumb_dimensions[0] / $divider;
                    } else {
                        $attach_thumb_dimensions[0] = $smfgSettings['thumbnail_resolution'];
                        $attach_thumb_dimensions[1] = $smfgSettings['thumbnail_resolution'];;
                    }
                }
            }
            $attach_thumb_width = $attach_thumb_dimensions[0];
            $attach_thumb_height = $attach_thumb_dimensions[1];

            // Check if local storage of remote images is enabled
            if ($smfgSettings['store_remote_images_locally']) {
                $context['display_as_remote'] = 0;
                $target = $processed;
            } else {
                $context['display_as_remote'] = 1;
            }

            // Determine if the file is an image
            $attach_dimensions = getimagesize($target);
            if ($attach_dimensions[2] == 1 || 2 || 3 || 4) {
                $attach_is_image = 1;
            } else {
                $attach_is_image = 0;
            }

            // Prep the URL for query
            $url_image = urlencode($file['url_image']);

            $request = $smcFunc['db_insert']('insert',
                '{db_prefix}garage_images',
                array(
                    'vehicle_id' => 'int',
                    'attach_location' => 'string',
                    'attach_ext' => 'string',
                    'attach_file' => 'string',
                    'attach_thumb_location' => 'string',
                    'attach_thumb_width' => 'int',
                    'attach_thumb_height' => 'int',
                    'attach_is_image' => 'int',
                    'attach_date' => 'int',
                    'attach_filesize' => 'int',
                    'attach_thumb_filesize' => 'int',
                    'attach_desc' => 'string',
                    'is_remote' => 'int',
                ),
                array(
                    $context['vehicle_id'],
                    $attach_filename,
                    $ext,
                    $url_image,
                    $attach_thumb_filename,
                    $attach_thumb_width,
                    $attach_thumb_height,
                    $attach_is_image,
                    $context['date_created'],
                    $attach_filesize,
                    $attach_thumb_filesize,
                    $file['attach_desc'],
                    $context['display_as_remote'],
                ),
                array(// no data
                )
            );
            $context['image_id'] = $smcFunc['db_insert_id']($request);
        }
    }
}

// Go get a remote image and store it locally
function getRemoteImage($url_image, $target)
{
    $ch = curl_init($url_image);
    $fw = fopen($target, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fw);
    // Exclude the header in the output
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
    fclose($fw);
}

// Fetch remote image attributes
function getimagesize_remote($image_url)
{
    global $smfgSettings;

    $ch = curl_init();
    $timeout = $smfgSettings['remote_timeout'];
    curl_setopt($ch, CURLOPT_URL, $image_url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

    // Getting binary data
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $image = curl_exec($ch);
    curl_close($ch);

    $im = ImageCreateFromString($image);
    if (!$im) {
        return false;
    }
    $gis[0] = ImageSX($im);
    $gis[1] = ImageSY($im);
    // array member 3 is used below to keep with current getimagesize standards
    $gis[3] = "width={$gis[0]} height={$gis[1]}";
    ImageDestroy($im);
    return $gis;
}

// Verify remote image exists
function url_validate($link)
{
    $url_parts = @parse_url($link);

    if (empty($url_parts["host"])) {
        return (false);
    }

    if (!empty($url_parts["path"])) {
        $documentpath = $url_parts["path"];
    } else {
        $documentpath = "/";
    }

    if (!empty($url_parts["query"])) {
        $documentpath .= "?" . $url_parts["query"];
    }

    $host = $url_parts["host"];
    $port = $url_parts["port"];
    // Now (HTTP-)GET $documentpath at $host";

    if (empty($port)) {
        $port = "80";
    }
    $socket = @fsockopen($host, $port, $errno, $errstr, 30);
    if (!$socket) {
        return (false);
    } else {
        fwrite($socket, "HEAD " . $documentpath . " HTTP/1.0\r\nHost: $host\r\n\r\n");
        //fputs ($socket, "HEAD ".$documentpath." HTTP/1.0\r\n");
        $http_response = fgets($socket, 22);

        if (preg_match("200 OK", $http_response, $regs)) {
            return (true);
            fclose($socket);
        } else {
//                echo "HTTP-Response: $http_response<br>";
            return (false);
        }
    }
}

// Verify remote image exists
function remoteImageExists($url)
{
    $addy = parse_url($url);
    $addy['port'] = isset($addy['port']) ? $addy['port'] : 80;
    $sh = fsockopen($addy['host'], $addy['port']) or die('cant open socket');
    fputs($sh, "HEAD {$addy['path']} HTTP/1.1\r\nHost: {$addy['host']}\r\n\r\n");
    while ($line = fgets($sh)) {
        if (preg_match('/^Content-Length: (d+)/', $line, $m)) {
            $size = $m[1];
        }
    }
    if (isset($size)) {
        return true;
    } else {
        return false;
    }
}

// Generate DIVs for managing models
function model_divs()
{

    global $smcFunc, $context, $scripturl, $txt, $settings;

    // This is gonna be needed...
    loadLanguage('Garage');

    $output = '';

    $request = $smcFunc['db_query']('', '
    SELECT id, make, pending
    FROM {db_prefix}garage_makes
    ORDER BY make ASC',
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['makes'][$count]['id'],
            $context['makes'][$count]['title'],
            $context['makes'][$count]['pending']) = $row;

        // Add appropriate 0s to the id for correct formatting
        $context['makes'][$count]['id_length'] = strlen($context['makes'][$count]['id']);
        if ($context['makes'][$count]['id_length'] == 1) {
            $context['makes'][$count]['name'] = "00" . $context['makes'][$count]['id'];
        } else {
            if ($context['makes'][$count]['id_length'] == 2) {
                $context['makes'][$count]['name'] = "0" . $context['makes'][$count]['id'];
            } else {
                if ($context['makes'][$count]['id_length'] == 3) {
                    $context['makes'][$count]['name'] = $context['makes'][$count]['id'];
                }
            }
        }

        $output .= '<div class="models_panel" id="makes' . $context['makes'][$count]['name'] . '" style="display: none;">';
        $output .= '
                    <table border="0" cellpadding="3" cellspacing="1" width="100%">';

        $request2 = $smcFunc['db_query']('', '
            SELECT id, model, pending
            FROM {db_prefix}garage_models
            WHERE make_id = {int:make_id}
            ORDER BY model ASC',
            array(
                'make_id' => $context['makes'][$count]['id'],
            )
        );
        $count2 = 0;
        while ($row2 = $smcFunc['db_fetch_row']($request2)) {
            list($context['models'][$count][$count2]['id'],
                $context['models'][$count][$count2]['title'],
                $context['models'][$count][$count2]['pending']) = $row2;

            $output .= '
                                <tr class="tableRow">
                                    <td align="left" valign="middle" nowrap="nowrap">' . $context['models'][$count][$count2]['title'] . '</td>
                                    <td align="right" width="75%" valign="middle">';

            if ($context['models'][$count][$count2]['pending'] == '1') {
                $output .= '<img src="' . $settings['default_images_url'] . '/icon_garage_disapprove_disabled.gif" alt="Disapprove" title="Disapprove" />';
                $output .= '<form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=model_approve;mdid=' . $context['models'][$count][$count2]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_model_' . $context['models'][$count][$count2]['id'] . '" id="approve_model_' . $context['models'][$count][$count2]['id'] . '" style="display: inline;">
                                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=makesmodels#models" />
                                                        <a href="#" onClick="document.approve_model_' . $context['models'][$count][$count2]['id'] . '.submit(); return false;"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                                    </form>';
            } else {
                $output .= '<form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=model_disable;mdid=' . $context['models'][$count][$count2]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="disable_model_' . $context['models'][$count][$count2]['id'] . '" id="disable_model_' . $context['models'][$count][$count2]['id'] . '" style="display: inline;">
                                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=makesmodels#models" />
                                                        <a href="#" onClick="document.disable_model_' . $context['models'][$count][$count2]['id'] . '.submit(); return false;"><img src="' . $settings['default_images_url'] . '/icon_garage_disapprove.gif" alt="' . $txt['smfg_disable'] . '" title="' . $txt['smfg_disable'] . '" /></a>
                                                    </form>';
                $output .= '<img src="' . $settings['default_images_url'] . '/icon_garage_approve_disabled.gif" alt="Approve" title="Approve" />&nbsp;';
            }

            $output .= '<a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=model_edit;mdid=' . $context['models'][$count][$count2]['id'] . '"><img src="' . $settings['default_images_url'] . '/icon_edit.gif" alt="Edit" title="Edit" /></a>';
            $output .= '<form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=model_delete;mdid=' . $context['models'][$count][$count2]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_model_' . $context['models'][$count][$count2]['id'] . '" id="remove_model_' . $context['models'][$count][$count2]['id'] . '" style="display: inline;">
                                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=makesmodels#models" />
                                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_model'] . '\')) { document.remove_model_' . $context['models'][$count][$count2]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                                </form>';

            $output .= '
                                    </td>
                                </tr>';
            $count2++;
        }
        $smcFunc['db_free_result'] ($request2);
        $count++;
        $output .= '
                            </table>';
        $output .= "
                    </div>";
    }
    $smcFunc['db_free_result'] ($request);

    return $output;

}

// Generate DIVs for managing products
function product_divs()
{

    global $smcFunc, $context, $scripturl, $txt, $settings;

    // This is gonna be needed...
    loadLanguage('Garage');

    $output = '';

    $request = $smcFunc['db_query']('', '
    SELECT id, title
    FROM {db_prefix}garage_business
    WHERE product = 1
    ORDER BY title ASC',
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['manufacturers'][$count]['id'],
            $context['manufacturers'][$count]['title']) = $row;

        // Add appropriate 0s to the id for correct formatting
        $context['manufacturers'][$count]['id_length'] = strlen($context['manufacturers'][$count]['id']);
        if ($context['manufacturers'][$count]['id_length'] == 1) {
            $context['manufacturers'][$count]['name'] = "00" . $context['manufacturers'][$count]['id'];
        } else {
            if ($context['manufacturers'][$count]['id_length'] == 2) {
                $context['manufacturers'][$count]['name'] = "0" . $context['manufacturers'][$count]['id'];
            } else {
                if ($context['manufacturers'][$count]['id_length'] == 3) {
                    $context['manufacturers'][$count]['name'] = $context['manufacturers'][$count]['id'];
                }
            }
        }

        $output .= '<div class="products_panel" id="man' . $context['manufacturers'][$count]['name'] . '" style="display: none;">';
        $output .= '
                    <table border="0" cellpadding="3" cellspacing="1" width="100%" class="bordercolor">';

        $request2 = $smcFunc['db_query']('', '
            SELECT DISTINCT c.id, c.title, c.field_order
            FROM {db_prefix}garage_categories AS c, {db_prefix}garage_products AS p
            WHERE p.business_id = {int:business_id}
                AND p.category_id = c.id
                ORDER BY c.field_order ASC',
            array(
                'business_id' => $context['manufacturers'][$count]['id'],
            )
        );
        $count2 = 0;
        while ($row2 = $smcFunc['db_fetch_row']($request2)) {
            list($context['categories'][$count2]['id'],
                $context['categories'][$count2]['title']) = $row2;

            $output .= '<tr>
                                    <td colspan="2"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $context['categories'][$count2]['title'] . '
                                    </span></h4></div></td>
                                 </tr>';

            $request3 = $smcFunc['db_query']('', '
                        SELECT id, title, pending
                        FROM {db_prefix}garage_products
                        WHERE business_id = {int:business_id}
                            AND category_id = {int:category_id}',
                array(
                    'business_id' => $context['manufacturers'][$count]['id'],
                    'category_id' => $context['categories'][$count2]['id'],
                )
            );
            $count3 = 0;
            while ($row3 = $smcFunc['db_fetch_row']($request3)) {
                list($context['products'][$count3]['id'],
                    $context['products'][$count3]['title'],
                    $context['products'][$count3]['pending']) = $row3;

                $output .= '
                                <tr class="tableRow">
                                    <td align="left" width="95%" valign="middle">' . $context['products'][$count3]['title'] . '</td>
                                    <td align="right" valign="middle" nowrap="nowrap">';

                if ($context['products'][$count3]['pending'] == '1') {
                    $output .= '<img src="' . $settings['default_images_url'] . '/icon_garage_disapprove_disabled.gif" alt="Disapprove" title="Disapprove" />';
                    $output .= '<form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=product_approve;pid=' . $context['products'][$count3]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_product_' . $context['products'][$count3]['id'] . '" id="approve_product_' . $context['products'][$count3]['id'] . '" style="display: inline;">
                                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=products" />
                                                        <a href="#" onClick="document.approve_product_' . $context['products'][$count3]['id'] . '.submit(); return false;"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                                    </form>';
                } else {
                    $output .= '<form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=product_disable;pid=' . $context['products'][$count3]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="disable_product_' . $context['products'][$count3]['id'] . '" id="disable_product_' . $context['products'][$count3]['id'] . '" style="display: inline;">
                                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=products" />
                                                        <a href="#" onClick="document.disable_product_' . $context['products'][$count3]['id'] . '.submit(); return false;"><img src="' . $settings['default_images_url'] . '/icon_garage_disapprove.gif" alt="' . $txt['smfg_disable'] . '" title="' . $txt['smfg_disable'] . '" /></a>
                                                    </form>';
                    $output .= '<img src="' . $settings['default_images_url'] . '/icon_garage_approve_disabled.gif" alt="Approve" title="Approve" />&nbsp;';
                }

                $output .= '<a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=product_edit;pid=' . $context['products'][$count3]['id'] . '"><img src="' . $settings['default_images_url'] . '/icon_edit.gif" alt="Edit" title="Edit" /></a>';
                $output .= '<form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=product_delete;pid=' . $context['products'][$count3]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_product_' . $context['products'][$count3]['id'] . '" id="remove_product_' . $context['products'][$count3]['id'] . '" style="display: inline;">
                                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=products" />
                                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_product'] . '\')) { document.remove_product_' . $context['products'][$count3]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                                </form>';

                $output .= '
                                    </td>
                                </tr>';
                $count3++;
            }
            $smcFunc['db_free_result'] ($request3);
            $count2++;
        }
        $smcFunc['db_free_result'] ($request2);
        $count++;
        $output .= '
                            </table>';
        $output .= "
                    </div>";
    }
    $smcFunc['db_free_result']($request);

    return $output;

}

// Validate the vehicle owner
function checkOwner($VID)
{
    global $smcFunc, $context;

    // Make sure this is their vehicle, dont want people editing other peoples' vehicles do we?
    $request = $smcFunc['db_query']('', '
        SELECT user_id
        FROM {db_prefix}garage_vehicles
        WHERE id = {int:vid}',
        array(
            'vid' => $VID,
        )
    );
    list($context['user_id']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result'] ($request);

    if ($context['user_id'] != $context['user']['id'] && !allowedTo('edit_all_vehicles')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_owner_error', true);
    }
}

// Check gallery limits
function checkLimits($VID, $gallery, $id = 0, $module = 'image')
{
    global $smcFunc, $smfgSettings;

    switch ($module) {
        case 'image':
            if ($gallery == 'garage') {
                $request = $smcFunc['db_query']('', '
                    SELECT COUNT(id)
                    FROM {db_prefix}garage_vehicles_gallery
                    WHERE vehicle_id = {int:vid}',
                    array(
                        'vid' => $VID,
                    )
                );
            } else {
                if ($gallery == 'mod') {
                    $request = $smcFunc['db_query']('', '
                    SELECT COUNT(id)
                    FROM {db_prefix}garage_modifications_gallery
                    WHERE vehicle_id = {int:vid}
                        AND modification_id = {int:modification_id}',
                        array(
                            'vid' => $VID,
                            'modification_id' => $id,
                        )
                    );
                } else {
                    if ($gallery == 'qmile') {
                        $request = $smcFunc['db_query']('', '
                    SELECT COUNT(id)
                    FROM {db_prefix}garage_quartermiles_gallery
                    WHERE vehicle_id = {int:vid}
                        AND quartermile_id = {int:quartermile_id}',
                            array(
                                'vid' => $VID,
                                'quartermile_id' => $id,
                            )
                        );
                    } else {
                        if ($gallery == 'dynorun') {
                            $request = $smcFunc['db_query']('', '
                    SELECT COUNT(id)
                    FROM {db_prefix}garage_dynoruns_gallery
                    WHERE vehicle_id = {int:vid}
                        AND dynorun_id = {int:dynorun_id}',
                                array(
                                    'vid' => $VID,
                                    'dynorun_id' => $id,
                                )
                            );
                        } else {
                            if ($gallery == 'lap') {
                                $request = $smcFunc['db_query']('', '
                    SELECT COUNT(id)
                    FROM {db_prefix}garage_laps_gallery
                    WHERE vehicle_id = {int:vid}
                        AND lap_id = {int:lap_id}',
                                    array(
                                        'vid' => $VID,
                                        'lap_id' => $id,
                                    )
                                );
                            }
                        }
                    }
                }
            }
            list($context['total']) = $smcFunc['db_fetch_row']($request);
            $smcFunc['db_free_result'] ($request);

            if ($context['total'] >= $smfgSettings['gallery_limit'] && !allowedTo('limit_exemption')) {
                loadLanguage('Errors');
                fatal_lang_error('garage_gallery_limit_error', false);
            }
            break;
        case 'video':
            if ($gallery == 'garage') {
                $request = $smcFunc['db_query']('', '
                    SELECT COUNT(id)
                    FROM {db_prefix}garage_video_gallery
                    WHERE vehicle_id = {int:vid}
                        AND type = "vehicle"',
                    array(
                        'vid' => $VID,
                    )
                );
            } else {
                if ($gallery == 'mod') {
                    $request = $smcFunc['db_query']('', '
                    SELECT COUNT(id)
                    FROM {db_prefix}garage_video_gallery
                    WHERE vehicle_id = {int:vid}
                        AND type = "mod"
                        AND type_id = {int:type_id}',
                        array(
                            'vid' => $VID,
                            'type_id' => $id,
                        )
                    );
                } else {
                    if ($gallery == 'qmile') {
                        $request = $smcFunc['db_query']('', '
                    SELECT COUNT(id)
                    FROM {db_prefix}garage_video_gallery
                    WHERE vehicle_id = {int:vid}
                        AND type = "qmile"
                        AND type_id = {int:type_id}',
                            array(
                                'vid' => $VID,
                                'type_id' => $id,
                            )
                        );
                    } else {
                        if ($gallery == 'dynorun') {
                            $request = $smcFunc['db_query']('', '
                    SELECT COUNT(id)
                    FROM {db_prefix}garage_video_gallery
                    WHERE vehicle_id = {int:vid}
                        AND type = "dynorun"
                        AND type_id = {int:type_id}',
                                array(
                                    'vid' => $VID,
                                    'type_id' => $id,
                                )
                            );
                        } else {
                            if ($gallery == 'lap') {
                                $request = $smcFunc['db_query']('', '
                    SELECT COUNT(id)
                    FROM {db_prefix}garage_video_gallery
                    WHERE vehicle_id = {int:vid}
                        AND type = "lap"
                        AND type_id = {int:type_id}',
                                    array(
                                        'vid' => $VID,
                                        'type_id' => $id,
                                    )
                                );
                            }
                        }
                    }
                }
            }
            list($context['total']) = $smcFunc['db_fetch_row']($request);
            $smcFunc['db_free_result'] ($request);

            if ($context['total'] >= $smfgSettings['gallery_limit_video'] && !allowedTo('limit_exemption')) {
                loadLanguage('Errors');
                fatal_lang_error('garage_gallery_limit_video_error', false);
            }
            break;
    }
}

// Check vehicle quota
function checkQuota($user_id)
{
    global $smcFunc, $smfgSettings;

    $request = $smcFunc['db_query']('', '
        SELECT COUNT(id)
        FROM {db_prefix}garage_vehicles
        WHERE user_id = {int:user_id}',
        array(
            'user_id' => $user_id,
        )
    );
    list($total_vehicles) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_fetch_row']($request);

    if ($total_vehicles >= $smfgSettings['default_vehicle_quota'] && !allowedTo('limit_exemption')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_vehicle_limit_error', false);
    }

}

// Return the VID of the featured vehicle
function getFeaturedVehicle()
{
    global $smcFunc, $smfgSettings, $modSettings;

    // By VID, return the VID supplied
    if ($smfgSettings['enable_featured_vehicle'] == 1) {

        return $smfgSettings['featured_vehicle_id'];

    } // From Block
    else {
        if ($smfgSettings['enable_featured_vehicle'] == 2) {

            // Newest Vehicle
            if ($smfgSettings['featured_vehicle_from_block'] == 1) {

                $request = $smcFunc['db_query']('', '
                SELECT v.id
                FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
                WHERE v.pending != "1"
                    AND mk.pending != "1"
                    AND md.pending != "1"
                ORDER BY v.date_created DESC
                LIMIT 1',
                    array(// no values
                    )
                );
                list($VID) = $smcFunc['db_fetch_row']($request);
                $smcFunc['db_free_result'] ($request);

                return $VID;

            } // Last Update Vehicle
            else {
                if ($smfgSettings['featured_vehicle_from_block'] == 2) {

                    $request = $smcFunc['db_query']('', '
                SELECT v.id
                FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
                WHERE v.pending != "1"
                    AND mk.pending != "1"
                    AND md.pending != "1"
                ORDER BY v.date_updated DESC
                LIMIT 1',
                        array(// no values
                        )
                    );
                    list($VID) = $smcFunc['db_fetch_row']($request);
                    $smcFunc['db_free_result'] ($request);

                    return $VID;

                } // Newest Modification
                else {
                    if ($smfgSettings['featured_vehicle_from_block'] == 3) {

                        $request = $smcFunc['db_query']('', '
                SELECT m.vehicle_id
                FROM {db_prefix}garage_modifications AS m, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
                WHERE m.vehicle_id = v.id
                    AND m.pending != "1"
                    AND v.pending != "1"
                    AND mk.pending != "1"
                    AND md.pending != "1"
                ORDER BY m.date_created DESC
                LIMIT 1',
                            array(// no values
                            )
                        );
                        list($VID) = $smcFunc['db_fetch_row']($request);
                        $smcFunc['db_free_result'] ($request);

                        return $VID;

                    } // Last Updated Modification
                    else {
                        if ($smfgSettings['featured_vehicle_from_block'] == 4) {

                            $request = $smcFunc['db_query']('', '
                SELECT m.vehicle_id
                FROM {db_prefix}garage_modifications AS m, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
                WHERE m.vehicle_id = v.id
                    AND m.pending != "1"
                    AND v.pending != "1"
                    AND mk.pending != "1"
                    AND md.pending != "1"
                ORDER BY m.date_updated DESC
                LIMIT 1',
                                array(// no values
                                )
                            );
                            list($VID) = $smcFunc['db_fetch_row']($request);
                            $smcFunc['db_free_result'] ($request);

                            return $VID;

                        } // Most Modified Vehicle
                        else {
                            if ($smfgSettings['featured_vehicle_from_block'] == 5) {

                                $request = $smcFunc['db_query']('', '
                SELECT v.id, COUNT( m.id ) AS total_mods
                FROM {db_prefix}garage_vehicles AS v 
                LEFT OUTER JOIN {db_prefix}garage_modifications AS m ON v.id = m.vehicle_id, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_products AS p, {db_prefix}garage_business AS b
                WHERE v.make_id = mk.id
                    AND v.model_id = md.id
                    AND m.product_id = p.id
                    AND p.business_id = b.id
                    AND mk.pending != "1"
                    AND md.pending != "1"
                    AND v.pending != "1"
                    AND m.pending != "1"
                    AND p.pending != "1"
                    AND b.pending != "1"
                    GROUP BY v.id
                    ORDER BY total_mods DESC
                    LIMIT 1',
                                    array(// no values
                                    )
                                );
                                list($VID) = $smcFunc['db_fetch_row']($request);
                                $smcFunc['db_free_result'] ($request);

                                return $VID;

                            } // Most Money Spent
                            else {
                                if ($smfgSettings['featured_vehicle_from_block'] == 6) {

                                    // *************************************************************
                                    // WARNING: The query check is being disabled to allow for the following subselect.
                                    // It is imperative this is turned back on for security reasons.
                                    // *************************************************************
                                    $modSettings['disableQueryCheck'] = 1;
                                    // *************************************************************

                                    $request = $smcFunc['db_query']('', '
            SELECT v.id, IFNULL(m.total_mods,0) + IFNULL(s.total_service,0) AS total_spent
                FROM {db_prefix}garage_vehicles AS v
                LEFT OUTER JOIN (
                    SELECT vehicle_id, SUM(price) + SUM(install_price) AS total_mods
                    FROM {db_prefix}garage_modifications AS m1, {db_prefix}garage_business AS b, {db_prefix}garage_products AS p
                    WHERE m1.manufacturer_id = b.id
                        AND m1.product_id = p.id
                        AND b.pending != "1"
                        AND m1.pending != "1"
                        AND p.pending != "1"
                    GROUP BY vehicle_id) AS m ON v.id = m.vehicle_id
                LEFT OUTER JOIN (
                    SELECT vehicle_id, SUM(price) AS total_service
                    FROM {db_prefix}garage_service_history AS s1, {db_prefix}garage_business AS b1
                    WHERE s1.garage_id = b1.id
                        AND b1.pending != "1"
                    GROUP BY vehicle_id) AS s ON v.id = s.vehicle_id, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
                WHERE v.make_id = mk.id
                    AND v.model_id = md.id
                    AND mk.pending != "1"
                    AND md.pending != "1"
                    AND v.pending != "1"
                    GROUP BY v.id, IFNULL(m.total_mods,0) + IFNULL(s.total_service,0)
                    ORDER BY total_spent DESC
                LIMIT 1',
                                        array(// no values
                                        )
                                    );
                                    list($VID) = $smcFunc['db_fetch_row']($request);
                                    $smcFunc['db_free_result'] ($request);

                                    // *************************************************************
                                    // WARNING: The query check is being enabled, this MUST BE DONE!
                                    // *************************************************************
                                    $modSettings['disableQueryCheck'] = 0;
                                    // *************************************************************

                                    return $VID;

                                } // Most Viewed Vehicle
                                else {
                                    if ($smfgSettings['featured_vehicle_from_block'] == 7) {

                                        $request = $smcFunc['db_query']('', '
                SELECT v.id
                FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
                WHERE v.pending != "1"
                    AND mk.pending != "1"
                    AND md.pending != "1"
                ORDER BY v.views DESC
                LIMIT 1',
                                            array(// no values
                                            )
                                        );
                                        list($VID) = $smcFunc['db_fetch_row']($request);
                                        $smcFunc['db_free_result'] ($request);

                                        return $VID;

                                    } // Latest Vehicle Comment
                                    else {
                                        if ($smfgSettings['featured_vehicle_from_block'] == 8) {

                                            $request = $smcFunc['db_query']('', '
                SELECT gb.vehicle_id
                FROM {db_prefix}garage_guestbooks AS gb, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
                WHERE gb.vehicle_id = v.id
                    AND v.pending != "1"
                    AND mk.pending != "1"
                    AND md.pending != "1"
                ORDER BY gb.post_date DESC
                LIMIT 1',
                                                array(// no values
                                                )
                                            );
                                            list($VID) = $smcFunc['db_fetch_row']($request);
                                            $smcFunc['db_free_result'] ($request);

                                            return $VID;

                                        } // Top Quartermile
                                        else {
                                            if ($smfgSettings['featured_vehicle_from_block'] == 9) {

                                                $request = $smcFunc['db_query']('', '
                SELECT q.vehicle_id
                FROM {db_prefix}garage_quartermiles AS q, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
                WHERE q.vehicle_id = v.id
                    AND v.pending != "1"
                    AND mk.pending != "1"
                    AND md.pending != "1"
                ORDER BY q.quart ASC
                LIMIT 1',
                                                    array(// no values
                                                    )
                                                );
                                                list($VID) = $smcFunc['db_fetch_row']($request);
                                                $smcFunc['db_free_result'] ($request);

                                                return $VID;

                                            } // Top Dynorun
                                            else {
                                                if ($smfgSettings['featured_vehicle_from_block'] == 10) {

                                                    $request = $smcFunc['db_query']('', '
                SELECT d.vehicle_id
                FROM {db_prefix}garage_dynoruns AS d, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
                WHERE d.vehicle_id = v.id
                    AND v.pending != "1"
                    AND mk.pending != "1"
                    AND md.pending != "1"
                ORDER BY d.bhp DESC
                LIMIT 1',
                                                        array(// no values
                                                        )
                                                    );
                                                    list($VID) = $smcFunc['db_fetch_row']($request);
                                                    $smcFunc['db_free_result'] ($request);

                                                    return $VID;

                                                } // Top Rated Vehicle
                                                else {
                                                    if ($smfgSettings['featured_vehicle_from_block'] == 11) {

                                                        if ($smfgSettings['rating_system'] == 0) {
                                                            $ratingfunc = "SUM";
                                                        } else {
                                                            if ($smfgSettings['rating_system'] == 1) {
                                                                $ratingfunc = "AVG";
                                                            }
                                                        }

                                                        $request = $smcFunc['db_query']('', '
                SELECT r.vehicle_id, ' . $ratingfunc . '( r.rating ) AS rating, COUNT( r.id ) * 10 AS poss_rating
                FROM {db_prefix}garage_ratings AS r, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
                WHERE r.vehicle_id = v.id
                    AND v.pending != "1"
                    AND mk.pending != "1"
                    AND md.pending != "1"
                    GROUP BY r.vehicle_id
                    ORDER BY r.rating DESC
                    LIMIT 1',
                                                            array(// no values
                                                            )
                                                        );
                                                        list($VID) = $smcFunc['db_fetch_row']($request);
                                                        $smcFunc['db_free_result'] ($request);

                                                        return $VID;

                                                    } // Top Laptime
                                                    else {
                                                        if ($smfgSettings['featured_vehicle_from_block'] == 12) {

                                                            $request = $smcFunc['db_query']('', '
                SELECT l.vehicle_id, CONCAT_WS( ":", l.minute, l.second, l.millisecond ) AS time
                FROM {db_prefix}garage_laps AS l, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
                WHERE l.vehicle_id = v.id
                    AND v.pending != "1"
                    AND mk.pending != "1"
                    AND md.pending != "1"
                ORDER BY l.time ASC
                LIMIT 1',
                                                                array(// no values
                                                                )
                                                            );
                                                            list($VID) = $smcFunc['db_fetch_row']($request);
                                                            $smcFunc['db_free_result'] ($request);

                                                            return $VID;

                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

        } // Random
        else {
            if ($smfgSettings['enable_featured_vehicle'] == 3) {

                // *************************************************************
                // WARNING: The query check is being disabled to allow for the following subselect.
                // It is imperative this is turned back on for security reasons.
                // *************************************************************
                $modSettings['disableQueryCheck'] = 1;
                // *************************************************************

                // If they choose to have featured vehicles with out images set retry limit 0, else retry limit 3
                if (!$smfgSettings['featured_vehicle_image_required']) {
                    $smfg_fv_retry_limit = 0;
                } else {
                    $smfg_fv_retry_limit = 3;
                }

                // Incase no vehicle higher then the rand doesn't have an image, keep trying, limited to retry limit
                $count = 0;
                $VID = "";
                while ($count <= $smfg_fv_retry_limit && empty($VID)) {
                    if ($count != $smfg_fv_retry_limit && $smfgSettings['featured_vehicle_image_required']) {
                        $extra = " AND gal.vehicle_id = v.id AND gal.hilite = 1";
                    } else {
                        $extra = "";
                    }

                    // now query for id greater then random no
                    $request = $smcFunc['db_query']('', '
            SELECT v.id
            FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_vehicles_gallery AS gal
            WHERE v.pending != "1"
                AND mk.pending != "1"
                AND md.pending != "1"
                AND mk.id = v.make_id
                AND md.id = v.model_id
                ' . $extra . '
                ORDER BY RAND()
            LIMIT 1',
                        array(// no values
                        )
                    );
                    list($VID) = $smcFunc['db_fetch_row']($request);
                    $smcFunc['db_free_result'] ($request);
                    $count++;
                }

                // *************************************************************
                // WARNING: The query check is being enabled, this MUST BE DONE!
                // *************************************************************
                $modSettings['disableQueryCheck'] = 0;
                // *************************************************************

                return $VID;
            }
        }
    }
}

// Send out notifications for pending items
function sendGarageNotifications()
{
    global $context, $smfgSettings, $txt, $smcFunc;

    // This is gonna be needed...
    loadLanguage('Garage');

    require_once('Subs-Post.php');

    // PM notifications enabled?
    if ($smfgSettings['enable_pm_pending_notify']) {

        /*/ Get PM recipients
        $request = $smcFunc['db_query']('', "
            SELECT user_id
            FROM {db_prefix}garage_notifications
            WHERE pm_opt_out != 1",__FILE__,__LINE__);
        while($row = $smcFunc['db_fetch_row']($request)) {

            $recipients = array(
                'to' => array($row[0]),
                'bcc' => array()
            );
            //$recipients['to'][] = $row[0];
            //$recipients['bcc'] = '';
        }
        $smcFunc['db_free_result'] ($request);*/

        // Get PM recipients
        $recipientsTo = Array();
        $recipientsBcc = Array();
        $request = $smcFunc['db_query']('', '
            SELECT user_id  
            FROM {db_prefix}garage_notifications  
            WHERE pm_opt_out != 1',
            array(// no values
            )
        );
        while ($row = $smcFunc['db_fetch_row']($request)) {
            array_push($recipientsTo, $row[0]);
        }
        $recipients = array(
            'to' => $recipientsTo,
            'bcc' => $recipientsBcc
        );
        $smcFunc['db_free_result'] ($request);

        // Get sender info
        $request = $smcFunc['db_query']('', '
            SELECT member_name, real_name, email_address
            FROM {db_prefix}members
            WHERE id_member = {int:pending_sender}',
            array(
                'pending_sender' => $smfgSettings['pending_sender'],
            )
        );
        $row = $smcFunc['db_fetch_row']($request);
        $sender['id'] = $smfgSettings['pending_sender'];
        $sender['name'] = $row[0];
        $sender['username'] = $row[1];
        $sender['email'] = $row[2];
        $smcFunc['db_free_result'] ($request);

        // Send the PM
        if (!empty($recipients['to']) || !empty($recipients['bcc'])) {
            $context['send_log'] = sendpm($recipients, $smfgSettings['pending_subject'], $txt['smfg_pending_PM'], false,
                $sender);
        } else {
            $context['send_log'] = array(
                'sent' => array(),
                'failed' => array()
            );
        }
    }

    // Email notifications enabled?
    if ($smfgSettings['enable_email_pending_notify']) {

        // Get email recipients
        $request = $smcFunc['db_query']('', '
            SELECT u.email_address
            FROM {db_prefix}garage_notifications AS n, {db_prefix}members AS u
            WHERE n.email_opt_out != 1
                AND n.user_id = u.id_member',
            array(// no values
            )
        );
        $count = 0;
        while ($row = $smcFunc['db_fetch_row']($request)) {
            $to[$count] = $row[0];
            $count++;
        }
        $smcFunc['db_free_result'] ($request);

        // Send the mail
        if (!empty($to)) {
            sendmail($to, $smfgSettings['pending_subject'], $txt['smfg_pending_email'], null, null, true);
        }
    }
}

// Sends out notifications for everything else
function sendOtherNotifications($toId, $subject, $message)
{
    global $context, $smfgSettings, $txt, $smcFunc;

    // This is gonna be needed...
    loadLanguage('Garage');

    require_once('Subs-Post.php');

    // PM recipients
    $recipientsTo = Array(
        1 => $toId
    );
    $recipientsBcc = Array();
    $recipients = array(
        'to' => $recipientsTo,
        'bcc' => $recipientsBcc
    );

    // Get sender info
    $request = $smcFunc['db_query']('', '
        SELECT member_name, real_name
        FROM {db_prefix}members
        WHERE id_member = {int:pending_sender}',
        array(
            'pending_sender' => $smfgSettings['pending_sender'],
        )
    );
    $row = $smcFunc['db_fetch_row']($request);
    $sender['id'] = $smfgSettings['pending_sender'];
    $sender['name'] = $row[0];
    $sender['username'] = $row[1];
    $smcFunc['db_free_result'] ($request);

    // Send the PM
    if (!empty($recipients['to']) || !empty($recipients['bcc'])) {
        $context['send_log'] = sendpm($recipients, $subject, $message, false, $sender);
    } else {
        $context['send_log'] = array(
            'sent' => array(),
            'failed' => array()
        );
    }
}

// Loads $smfgSettings array
function loadSmfgConfig()
{
    global $smcFunc, $smfgSettings, $smcFunc;

    // Load the $smfgSettings array.
    $request = $smcFunc['db_query']('', '
        SELECT config_name, config_value
        FROM {db_prefix}garage_config',
        array(// no values
        )
    );

    $smfgSettings = array();
    if (!$request) {
        db_fatal_error();
    }
    while ($row = $smcFunc['db_fetch_row']($request)) {
        $smfgSettings[$row[0]] = $row[1];
    }

    $smcFunc['db_free_result'] ($request);
}

// List directory contents
function directoryToArray($directory, $recursive)
{
    $array_items = array();
    if ($handle = opendir($directory)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                if (is_dir($directory . "/" . $file)) {
                    if ($recursive) {
                        $array_items = array_merge($array_items,
                            directoryToArray($directory . "/" . $file, $recursive));
                    }
                    //$file = $directory . "/" . $file;
                    //$array_items[] = preg_replace("/\/\//si", "/", $file);
                } else {
                    //$file = $directory . "/" . $file;
                    $array_items[] = preg_replace("/\/\//si", "/", $file);
                }
            }
        }
        closedir($handle);
    }
    return $array_items;
}

// Get the size of a directory
function getDirectorySize($path, $recursive = 1)
{
    $totalsize = 0;
    $totalcount = 0;
    $dircount = 0;
    if ($handle = opendir($path)) {
        while (false !== ($file = readdir($handle))) {
            $nextpath = $path . '/' . $file;
            if ($file != '.' && $file != '..' && !is_link($nextpath)) {
                if (is_dir($nextpath) && $recursive) {
                    $dircount++;
                    $result = getDirectorySize($nextpath);
                    $totalsize += $result['size'];
                    $totalcount += $result['count'];
                    $dircount += $result['dircount'];
                } elseif (is_file($nextpath)) {
                    $totalsize += filesize($nextpath);
                    $totalcount++;
                }
            }
        }
    }
    closedir($handle);
    $total['size'] = $totalsize;
    $total['count'] = $totalcount;
    $total['dircount'] = $dircount;
    return $total;
}

// Applies proper KB, MB, or GB to filesize
function sizeFormat($size)
{
    if ($size < 1024) {
        return $size . " bytes";
    } else {
        if ($size < (1024 * 1024)) {
            $size = round($size / 1024, 1);
            return $size . " KB";
        } else {
            if ($size < (1024 * 1024 * 1024)) {
                $size = round($size / (1024 * 1024), 1);
                return $size . " MB";
            } else {
                $size = round($size / (1024 * 1024 * 1024), 1);
                return $size . " GB";
            }
        }
    }

}

// Footer for SMF Garage
function smfg_footer()
{
    global $smfgSettings, $txt, $scripturl;

    $output = '
    <table border="0" width="100%">
        <tr>
            <td height="5"></td>
        </tr>
        <tr>
            <td align="center">
            <span class="smalltext">' . $txt['smfg_powered_by'] . ' ' . $smfgSettings['version'] . '</a> | <a href="' . $scripturl . '?action=garage;sa=copyright">' . $txt['smfg_copy'] . ' 2007-' . date('Y',
            time()) . '</span>
            </td>
        </tr>
    </table>';

    return $output;

}

// Returns embedded $type = flash player (1), thumbnail (2), video ID (3), validate video URL (4)
function displayVideo_OLD($url, $type)
{
    global $settings, $context;

    // Thanks to KarlBenson from simplemachines.org for code from his AutoEmbedVideoClips mod.
    // http://custom.simplemachines.org/mods/index.php?mod=977

    $embed = array();

    $embed[] = array(
        'name' => '123video.nl',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)123video\.nl/playvideos\.asp\?MovieID=([0-9]{1,8})(?:.*?)',
        'embedlink' => 'http://www.123video.nl/123video_share.swf?mediaSrc=$1',
        'width' => '420',
        'height' => '339',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'classid' => 'clsid:d27cdb6e-ae6d-11cf-96b8-444553540000',
    );
    $embed[] = array(
        'name' => 'Aniboom',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)aniboom\.com/Player.aspx\?v=([0-9]{1,8})(?:.*?)',
        'embedlink' => 'http://api.aniboom.com/embedded.swf?videoar=$1',
        'width' => '448',
        'height' => '372',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'AOL Uncut',
        'enabled' => 1,
        'pattern' => 'http://uncutvideo\.aol\.com/videos/([0-9a-f]{32})(?:.*?)',
        'embedlink' => 'http://uncutvideo.aol.com/v6.220/en-US/uc_videoplayer.swf?aID=1$1&site=http://uncutvideo.aol.com/',
        'width' => '415',
        'height' => '347',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'AtomFilms',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)atomfilms\.com/film/([0-9a-z_-]{1,40})\.jsp(?:.*?)',
        'embedlink' => 'http://www.atomfilms.com:80/a/autoplayer/shareEmbed.swf?keyword=$1',
        'width' => '426',
        'height' => '350',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'AtomFilms Uploads',
        'enabled' => 1,
        'pattern' => 'http://uploads\.atomfilms\.com/Clip\.aspx\?key=([0-9a-f]{1,16})(?:.*?)',
        'embedlink' => 'http://uploads.atomfilms.com/player.swf?key=$1',
        'width' => '430',
        'height' => '354',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'Biku',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)biku\.com/opus/(?:player.swf\?VideoID=|)([0-9]{1,8})(?:.*?)',
        'embedlink' => 'http://www.biku.com/opus/player.swf?VideoID=$1&embed=true&autoStart=false',
        'width' => '480',
        'height' => '395',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'BrightCove',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)brightcove\.(tv|com)/title.jsp\?title=([0-9]{1,12})(?:.*?)',
        'embedlink' => 'http://www.brightcove.$1/playerswf?allowFullScreen=true&initVideoId=$2&servicesURL=http://www.brightcove.tv'
            . '&viewerSecureGatewayURL=https://www.brightcove.tv&cdnURL=http://admin.brightcove.com&autoStart=false',
        'width' => '486',
        'height' => '412',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('swLiveConnect' => 'true'),
    );
    $embed[] = array(
        'name' => 'CellFish',
        'enabled' => 1,
        'pattern' => 'http://cellfish\.cellfish\.com/(?:video|multimedia)/([0-9]{1,8})(?:.*?)',
        'embedlink' => 'http://cellfish.com/static/swf/player.swf?Id=$1',
        'width' => '420',
        'height' => '315',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'ClipFish.de',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)clipfish\.de/(?:player.php|videoplayer\.swf)\?(?:.*?)videoid=([a-z0-9]{1,20})(?:.*?)',
        'embedlink' => 'http://www.clipfish.de/videoplayer.swf?as=0&videoid=$1&r=1&c=0067B3',
        'width' => '464',
        'height' => '380',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'classid' => 'clsid:d27cdb6e-ae6d-11cf-96b8-444553540000',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'CollegeHumor',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)collegehumor\.com/video:([0-9]{1,12})(?:.*?)',
        'embedlink' => 'http://www.collegehumor.com/moogaloop/moogaloop.swf?clip_id=$1',
        'width' => '480',
        'height' => '360',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'Dailymotion',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)dailymotion\.com/(?:.*?)video/([a-z0-9]{1,8})(?:.*?)',
        'embedlink' => 'http://www.dailymotion.com/swf/$1',
        'width' => '420',
        'height' => '335',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'Dave.tv',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)dave\.tv/MediaPlayer.aspx\?contentItemId=([0-9]{1,10})(?:.*?)',
        'embedlink' => 'http://dave.tv/dbox/dbox_small.swf?configURI=http://dave.tv/dbox/config.ashx&volume=50&channelContentId=$1',
        'width' => '300',
        'height' => '260',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'dv.ouou',
        'enabled' => 1,
        'pattern' => 'http://dv\.ouou\.com/(?:play/v_|v/)([a-f0-9]{14})(?:.*?)',
        'embedlink' => 'http://dv.ouou.com/v/$1',
        'width' => '480',
        'height' => '385',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'ESPN',
        'enabled' => 1,
        'pattern' => 'http://sports\.espn\.go\.com/broadband/video/videopage\?(?:.*?)videoId=([0-9]{1,10})(?:.*?)',
        'embedlink' => 'http://sports.espn.go.com/broadband/player.swf?mediaId=$1',
        'width' => '440',
        'height' => '361',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'Gametrailers',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)gametrailers\.com/player/([0-9]{1,8})\.html(?:.*?)',
        'embedlink' => 'http://www.gametrailers.com/remote_wrap.php?mid=$1',
        'width' => '480',
        'height' => '392',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'classid' => 'clsid:d27cdb6e-ae6d-11cf-96b8-444553540000',
        'extraparams' => array('swLiveConnect' => 'true'),
    );
    $embed[] = array(
        'name' => 'GameVideos',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)gamevideos\.com/(?:video/id/|video/embed\?video=)([0-9]{1,8})(?:.*?)',
        'embedlink' => 'http://gamevideos.com:80/swf/gamevideos11.swf?embedded=1&autoplay=0&src=http://gamevideos.com:80/video/videoListXML%3Fid%3D$1%26adPlay%3Dfalse',
        'width' => '420',
        'height' => '405',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'Glumbert',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)glumbert\.com/media/([a-z0-9_-]{1,30})(?:.*?)',
        'embedlink' => 'http://www.glumbert.com/embed/$1',
        'width' => '425',
        'height' => '335',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'Godtube',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)godtube\.com/view_video\.php\?viewkey=([0-9a-f]{20})(?:.*?)',
        'embedlink' => 'http://godtube.com/flvplayer.swf?viewkey=$1',
        'width' => '330',
        'height' => '270',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'Google Video',
        'enabled' => 1,
        'pattern' => 'http://video\.google\.(com|com\.au|co\.uk|de|es|fr|it|nl|pl|ca|cn)/(?:videoplay|url)\?docid=([0-9a-z-_]{1,20})(?:.*?)',
        'embedlink' => 'http://video.google.$1/googleplayer.swf?docId=$2',
        'width' => '400',
        'height' => '326',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'Guba',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)guba\.com/watch/([0-9]{1,12})(?:.*?)',
        'embedlink' => 'http://www.guba.com/f/root.swf?video_url=http://free.guba.com/uploaditem/$1/flash.flv&amp;isEmbeddedPlayer=true',
        'width' => '525',
        'height' => '360',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'Hulu.com (US Only)',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)hulu\.com/(?:embed/|playerembed\.swf\?pid=)([a-z0-9-_]{1,32})(?:.*?)',
        'embedlink' => 'http://www.hulu.com/embed/$1',
        'width' => '567',
        'height' => '350',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'IFilm',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)spike\.com/video/([0-9]{1,8})(?:.*?)',
        'embedlink' => 'http://www.spike.com/efp?flvbaseclip=$1',
        'width' => '448',
        'height' => '365',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'Koreus',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)koreus\.com/video/([0-9a-z-]{1,50})\.html(?:.*?)',
        'embedlink' => 'http://www.koreus.com/video/$1',
        'width' => '400',
        'height' => '300',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'Libero.it',
        'enabled' => 1,
        'pattern' => 'http://video\.libero\.it/app/play(?:/index.html|)\?id=([a-f0-9]{32})(?:.*?)',
        'embedlink' => 'http://video.libero.it/static/swf/eltvplayer.swf?id=$1.flv&ap=0',
        'width' => '400',
        'height' => '333',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'LiveLeak',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)liveleak\.com/view\?i=([0-9a-z]{3}_|)([a-z0-9]{10})(?:.*?)',
        'embedlink' => 'http://www.liveleak.com/player.swf?autostart=false&token=$1$2',
        'width' => '450',
        'height' => '370',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'LiveVideo',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)livevideo\.com/video/(?:view/|)(?:(?:.*?)/|)([0-9a-f]{32})(?:.*?)',
        'embedlink' => 'http://www.livevideo.com/flvplayer/embed/$1',
        'width' => '445',
        'height' => '369',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'Megavideo',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)megavideo\.com/\?v=([0-9a-z]{8})(?:.*?)',
        'embedlink' => 'http://www.megavideo.com/v/$1.0.0',
        'width' => '432',
        'height' => '351',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'MetaCafe',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)metacafe\.com/(?:watch|fplayer)/([0-9]{1,10})/(?:.*?)/',
        'embedlink' => 'http://www.metacafe.com/fplayer/$1/metacafe.swf',
        'width' => '400',
        'height' => '345',
        'thumb' => 'http://www.metacafe.com/thumb/$1.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'MSN Live/Soapbox Video',
        'enabled' => 1,
        'pattern' => 'http://(?:soapbox|video)\.msn\.com/video\.aspx\?(?:(?:.*?)vid=|from=msnvideo&showPlaylist=true&playlist=videoByUuids:uuids:)((?:[0-9a-z]{8})(?:(?:-(?:[0-9a-z]{4})){3})-(?:[0-9a-z]{12}))(?:.*?)',
        'embedlink' => 'http://images.video.msn.com/flash/soapbox1_1.swf?c=v&v=$1',
        'width' => '432',
        'height' => '364',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'Mofile',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|tv\.|)mofile\.com/([0-9a-z]{8})(?:.*?)',
        'embedlink' => 'http://tv.mofile.com/cn/xplayer.swf?v=$1',
        'width' => '480',
        'height' => '395',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'M Thai',
        'enabled' => 1,
        'pattern' => 'http://video\.mthai\.com/player\.php\?id=([0-9a-z]{14,20})(?:.*?)',
        'embedlink' => 'http://video.mthai.com/Flash_player/player.swf?idMovie=$1',
        'width' => '370',
        'height' => '330',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'MySpaceTv',
        'enabled' => 1,
        'pattern' => 'http://(?:vids\.myspace|myspacetv)\.com/index\.cfm\?fuseaction=vids\.individual&amp;VideoID=([0-9]{1,10})(?:.*?)',
        'embedlink' => 'http://lads.myspace.com/videos/myspacetv_vplayer0005.swf?m=$1&amp;type=video',
        'width' => '480',
        'height' => '386',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'MyVideo.de',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)myvideo\.de/watch/([0-9]{1,8})(?:.*?)',
        'embedlink' => 'http://www.myvideo.de/movie/$1',
        'width' => '470',
        'height' => '406',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'OnSmash',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|videos\.|)onsmash\.com/(?:v|e)/([0-9a-z]{16})(?:.*?)',
        'embedlink' => 'http://videos.onsmash.com/e/$1',
        'width' => '448',
        'height' => '374',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'Revver',
        'enabled' => 1,
        'pattern' => 'http://(?:one\.|www\.|)revver\.com/watch/([0-9]{1,8})(?:.*?)',
        'embedlink' => 'http://flash.revver.com/player/1.0/player.swf?mediaId=$1&affiliateId=0&allowFullScreen=true',
        'width' => '480',
        'height' => '392',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'Sevenload',
        'enabled' => 1,
        'pattern' => 'http://(en\.|tr\.|de\.|www\.|)sevenload\.com/(?:videos|videolar)/([0-9a-z]{1,8})(?:.*?)',
        'embedlink' => 'http://$1sevenload.com/pl/$2/425x350/swf',
        'width' => '425',
        'height' => '350',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'Stage6',
        'enabled' => 1,
        'pattern' => 'http://(?:(?:www\.)stage6|stage6\.divx)\.com/(?:.*?)/video/([0-9]{1,11})/(?:.*?)',
        'embedlink' => 'http://video.stage6.com/$1/',
        'src' => 1, // Special, Stage6 use src rather than video, this bool, tells me to use src instead
        //'width' => '640',
        'width' => '576',
        //'height' => '480',
        'height' => '432',
        'thumb' => 'http://images.stage6.com/video_images/$1t.jpg',
        'video_id' => '$1',
        'codebase' => 'http://go.divx.com/plugin/DivXBrowserPlugin.cab',
        'pluginspage' => 'http://go.divx.com/plugin/download/',
        'classid' => 'clsid:67DABFBF-D0AB-41fa-9C46-CC0F21721616',
        'type' => 'video/divx',
        'extraparams' => array(
            'custommode' => 'false',
            'showpostplaybackad' => 'false',
            'autoPlay' => 'false',
        ),
    );
    $embed[] = array(
        'name' => 'Streetfire.net',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|videos\.|)streetfire\.net/video/((?:[0-9a-z]{8})(?:(?:-(?:[0-9a-z]{4})){3})-(?:[0-9a-z]{12}))\.htm(?:.*?)',
        'embedlink' => 'http://videos.streetfire.net/vidiac.swf?video=$1',
        'width' => '428',
        'height' => '352',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'Tudou',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)tudou\.com/(?:programs/view/|v/)([a-z0-9-]{1,12})(?:.*?)',
        'embedlink' => 'http://www.tudou.com/v/$1',
        'width' => '400',
        'height' => '300',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'Veoh',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)veoh\.com/videos/([0-9a-z]{14,16})(?:.*?)',
        'embedlink' => 'http://www.veoh.com/videodetails2.swf?permalinkId=$1&id=anonymous&player=videodetailsembedded&videoAutoPlay=0',
        'width' => '540',
        'height' => '438',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'videotube.de',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)videotube\.de/watch/([0-9]{1,8})(?:.*?)',
        'embedlink' => 'http://www.videotube.de/flash/player.swf?baseURL=http%3A%2F%2Fwww.videotube.de%2Fwatch%2F$1',
        'width' => '480',
        'height' => '400',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'Vidiac',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)vidiac\.com/video/((?:[0-9a-z]{8})(?:(?:-(?:[0-9a-z]{4})){3})-(?:[0-9a-z]{12}))\.htm(?:.*?)',
        'embedlink' => 'http://www.vidiac.com/vidiac.swf?video=$1',
        'width' => '428',
        'height' => '352',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'VidMax',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)vidmax\.com/index\.php/videos/view/([0-9]{1,10})(?:.*?)',
        'embedlink' => 'http://vidmax.com/img/vidmax_player.swf?xml=http://vidmax.com/index.php/videos/playlist/&id=$1&autoPlay=true&bg=http://vidmax.com/img/back.jpg',
        'width' => '450',
        'height' => '447',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'Vimeo',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)vimeo\.com/([0-9]{1,12})(?:.*?)',
        'embedlink' => 'http://vimeo.com/moogaloop.swf?clip_id=$1&amp;server=vimeo.com&amp;fullscreen=1&amp;show_title=1'
            . '&amp;show_byline=1&amp;show_portrait=0&amp;color=01AAEA',
        'width' => '400',
        'height' => '225',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'VSocial',
        'enabled' => 1,
        'pattern' => 'http://(?:www\.|)vsocial\.com/video/\?d=([0-9]{1,8})(?:.*?)',
        'embedlink' => 'http://static.vsocial.com/flash/ups.swf?d=$1&a=0',
        'width' => '410',
        'height' => '400',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'Yahoo (Except HK)',
        'enabled' => 1,
        'pattern' => 'http://(?:(?:www|uk|fr|it|es|br|au|mx|de|ca)\.|)video\.yahoo\.com/video/play\?vid=([0-9]{1,10})(?:.*?)',
        'embedlink' => 'http://us.i1.yimg.com/cosmos.bcst.yahoo.com/player/media/swf/FLVVideoSolo.swf?id=$1',
        'width' => '425',
        'height' => '350',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'Yahoo HK Only',
        'enabled' => 1,
        'pattern' => 'http://hk\.video\.yahoo\.com/video/video\.html\?id=([0-9]{1,10})(?:.*?)',
        'embedlink' => 'http://w.video.hk.yahoo.net/video/dplayer.html?vid=$1',
        'width' => '420',
        'height' => '370',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'extraparams' => array('allowFullScreen' => 'true'),
    );
    $embed[] = array(
        'name' => 'Youku',
        'enabled' => 1,
        'pattern' => 'http://(?:v\.youku\.com/v_show/id_(?:[0-9a-z]{4})|player\.youku\.com/player\.php/sid/)([0-9a-z]{6,14})(?:.*?)',
        'embedlink' => 'http://player.youku.com/player.php/sid/$1=/v.swf',
        'width' => '450',
        'height' => '372',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'YouTube',
        'enabled' => 1,
        'pattern' => 'http://(?:(?:www|au|br|ca|es|fr|de|hk|ie|it|jp|mx|nl|nz|pl|ru|tw|uk)\.|)youtube\.com/(?:(?:watch|)\?v=|v/|jp\.swf\?video_id=)([0-9A-Za-z-_]{11})(?:.*?)',
        'embedlink' => 'http://www.youtube.com/v/$1',
        'width' => '425',
        'height' => '350',
        'thumb' => 'http://i.ytimg.com/vi/$1/default.jpg',
        'video_id' => '$1',
    );
    $embed[] = array(
        'name' => 'YouTube Playlist',
        'enabled' => 1,
        'pattern' => 'http://(?:(?:www|au|br|ca|es|fr|de|hk|ie|it|jp|mx|nl|nz|pl|ru|tw|uk)\.|)youtube\.com/(?:ep\.swf\?id=|view_play_list\?p=|p/)([0-9a-f]{16})(?:.*?)',
        'embedlink' => 'http://www.youtube.com/p/$1',
        'width' => '425',
        'height' => '355',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );

    // Now do the magic, convert those links in messages to automatically embed the videos
    foreach ($embed as $id => $arr) {
        // If this site is enabled and if we haven't reached our max replacements
        if ($arr['enabled']) {
            // Build the extraparams for <object> & <embed>
            $object = $objectparams = $embedparams = '';
            if (isset($arr['extraparams']) && is_array($arr['extraparams'])) {
                foreach ($arr['extraparams'] as $a => $b) {
                    $objectparams .= '
                        <param name="' . $a . '" value="' . $b . '" />';
                    $embedparams .= ' ' . $a . '="' . $b . '"';
                }
            }
            // Build the <object> (Non-Mac IE Only)
            if ($context['browser']['is_ie'] && !$context['browser']['is_mac_ie']) {
                $object = '<object' .
                    ' codebase="' . (empty($arr['codebase']) ? 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0' : $arr['codebase']) . '" ' .
                    (empty($arr['classid']) ? '' : 'classid="' . $arr['classid'] . '" ') .
                    'type="' . (empty($arr['type']) ? 'application/x-shockwave-flash' : $arr['type']) . '" ' .
                    'width="' . $arr['width'] . '" height="' . $arr['height'] . '">' .
                    '<param name="' . (empty($arr['src']) ? 'movie' : 'src') . '" value="' . $arr['embedlink'] . '" />' .
                    (!empty($objectparams) ? $objectparams : '') .
                    '<param name="wmode" value="transparent" /><param name="allowScriptAccess" value="never" />' .
                    '<param name="quality" value="high" /><param name="pluginspage" value="' . (empty($arr['pluginspage']) ? 'http://www.macromedia.com/go/getflashplayer' : $arr['pluginspage']) . '" />';
            }

            // Build the <embed>
            $object .= '
                        <embed type="' . (empty($arr['type']) ? 'application/x-shockwave-flash' : $arr['type']) . '" ' .
                'src="' . $arr['embedlink'] . '" width="' . $arr['width'] . '" height="' . $arr['height'] . '" ' .
                'AllowScriptAccess="never" quality="high" wmode="transparent"' .
                (!empty($embedparams) ? $embedparams : '') .
                ' />' .
                '<noembed><a href="' . $arr['embedlink'] . '" target="_blank">' . $arr['embedlink'] . '</a></noembed>';

            // If using <object> remember to close it
            if ($context['browser']['is_ie'] && !$context['browser']['is_mac_ie']) {
                $object .= '</object>';
            }

            // Tidy up
            unset($objectparams, $embedparams);

            if ($arr['name'] == 'Stage6') {
                $pattern = '#' . $arr['pattern'] . '(?:.*)#i';
            } else {
                $pattern = '#' . $arr['pattern'] . '(?:.*?)#i';
            }

            $myMatches = 0;

            if (preg_match($pattern, $url)) {
                switch ($type) {
                    case 1:
                        $output = preg_replace($pattern, $object, $url);
                        return $output;
                        break;
                    case 2:
                        $thumb = preg_replace($pattern, $arr['thumb'], $url);
                        return $thumb;
                        break;
                    case 3:
                        $video_id = preg_replace($pattern, $arr['video_id'], $url);
                        return $video_id;
                        break;
                    case 4:
                        $myMatches++;
                        return true;
                        break;
                    case 'height':
                        $height = preg_replace($pattern, $arr['height'], $url);
                        return $height;
                        break;
                    case 'width':
                        $width = preg_replace($pattern, $arr['width'], $url);
                        return $width;
                        break;
                }
            }

            // Tidy up
            unset($embed, $pattern, $object, $arr, $a, $b);
        }
    }
    if ($myMatches == 0) {
        return false;
    }
}

function garage_title_clean($string)
{
    $match_array = ARRAY('&', '"');
    $replace_array = ARRAY("&amp;", "&#34;");

    return str_replace($match_array, $replace_array, $string);
}

function smfg_trim($string, $maxlength = 20)
{
    $string_length = strlen($string);
    if ($string_length > $maxlength) {
        $string = substr($string, 0, $maxlength);
        $string .= "...";
    }

    return $string;
}

// Generates browse tables
function browse_tables($browse_type)
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['mootools'] = 1;
    $context['lightbox'] = 1;
    $context['editinplace'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;
    $context['garage_tips'] = 1;

    $context['date_format'] = $smfgSettings['dateformat'];
    $context['browse_type'] = $browse_type;
    $context['sub_template'] = 'browse_tables';

    if ($browse_type == 'vehicles') {
        // Check Permissions
        isAllowedTo('browse_vehicles');

        $context['linktree'][] = array(
            'url' => $scripturl . '?action=garage;sa=browse',
            'name' => &$txt['smfg_browse']
        );
    } else {
        if ($browse_type == 'quartermiles') {
            $context['linktree'][] = array(
                'url' => $scripturl . '?action=garage;sa=quartermiles',
                'name' => &$txt['smfg_quartmiles']
            );

            // Check Permissions
            isAllowedTo('browse_qms');

            // Make sure this module is enabled
            if (!$smfgSettings['enable_quartermile']) {
                loadLanguage('Errors');
                fatal_lang_error('garage_disabled_module_error', false);
            }
        } else {
            if ($browse_type == 'dynoruns') {
                $context['linktree'][] = array(
                    'url' => $scripturl . '?action=garage;sa=dynornus',
                    'name' => &$txt['smfg_dynoruns']
                );

                // Check Permissions
                isAllowedTo('browse_dynos');

                // Make sure this module is enabled
                if (!$smfgSettings['enable_dynorun']) {
                    loadLanguage('Errors');
                    fatal_lang_error('garage_disabled_module_error', false);
                }
            } else {
                if ($browse_type == "laps") {
                    $context['linktree'][] = array(
                        'url' => $scripturl . '?action=garage;sa=laptimes',
                        'name' => &$txt['smfg_laptimes']
                    );

                    // Check Permissions
                    isAllowedTo('browse_laps');

                    // Make sure this module is enabled
                    if (!$smfgSettings['enable_laptimes']) {
                        loadLanguage('Errors');
                        fatal_lang_error('garage_disabled_module_error', false);
                    }
                } else {
                    if ($browse_type == "modifications") {
                        $context['linktree'][] = array(
                            'url' => $scripturl . '?action=garage;sa=modifications',
                            'name' => &$txt['smfg_modifications']
                        );
                    } else {
                        if ($browse_type == "mostmodified") {
                            $context['linktree'][] = array(
                                'url' => $scripturl . '?action=garage;sa=mostmodified',
                                'name' => &$txt['smfg_most_modified']
                            );
                        } else {
                            if ($browse_type == "mostviewed") {
                                $context['linktree'][] = array(
                                    'url' => $scripturl . '?action=garage;sa=mostviewed',
                                    'name' => &$txt['smfg_most_viewed']
                                );
                            } else {
                                if ($browse_type == "latestservice") {
                                    $context['linktree'][] = array(
                                        'url' => $scripturl . '?action=garage;sa=latestservice',
                                        'name' => &$txt['smfg_latest_service']
                                    );
                                } else {
                                    if ($browse_type == "toprated") {
                                        $context['linktree'][] = array(
                                            'url' => $scripturl . '?action=garage;sa=toprated',
                                            'name' => &$txt['smfg_top_rated']
                                        );
                                    } else {
                                        if ($browse_type == "mostspent") {
                                            $context['linktree'][] = array(
                                                'url' => $scripturl . '?action=garage;sa=mostspent',
                                                'name' => &$txt['smfg_most_spent']
                                            );
                                        } else {
                                            if ($browse_type == "latestblog") {
                                                $context['linktree'][] = array(
                                                    'url' => $scripturl . '?action=garage;sa=latestblog',
                                                    'name' => &$txt['smfg_latest_blog']
                                                );
                                            } else {
                                                if ($browse_type == "latestvideo") {
                                                    $context['linktree'][] = array(
                                                        'url' => $scripturl . '?action=garage;sa=latestvideo',
                                                        'name' => &$txt['smfg_latest_video']
                                                    );
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // Set the first row class for alternating bg colors
    $context['bgclass'] = "windowbg2";

    // Display as - Vehicles
    if ($browse_type == 'vehicles') {

        // Set the defaults and GET variables for sorting and order
        // Order options
        $order = 'DESC';
        if (!isset($_GET['order'])) {
            $_GET['order'] = "";
        }
        if ($_GET['order'] == "ASC") {
            $order = 'ASC';
        } else {
            if ($_GET['order'] == "DESC") {
                $order = 'DESC';
            }
        }

        // Create the default sort option links
        $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=year\">" . $txt['smfg_year'] . "</a>";
        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=make\">" . $txt['smfg_make'] . "</a>";
        $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=model\">" . $txt['smfg_model'] . "</a>";
        $context['sort']['byColor'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=color\">" . $txt['smfg_color'] . "</a>";
        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=owner\">" . $txt['smfg_owner'] . "</a>";
        $context['sort']['byViews'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=views\">" . $txt['smfg_views'] . "</a>";
        $context['sort']['byMods'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=mods\">" . $txt['smfg_mods'] . "</a>";
        $context['sort']['byUpdated'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=updated\">" . $txt['smfg_updated'] . "</a>";

        // Build sort option links with dynamic ordering
        $sort = "v.date_created";
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "";
        }
        if ($_GET['sort'] == "date_created") {
            $sort = "v.date_created";
        } else {
            if ($_GET['sort'] == "updated") {
                $sort = "v.date_updated";
                // Set order options for each sort type
                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                    $context['sort']['byUpdated'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=updated;order=ASC\">" . $txt['smfg_updated'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                } else {
                    $context['sort']['byUpdated'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=updated;order=DESC\">" . $txt['smfg_updated'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                }
            } else {
                if ($_GET['sort'] == "owner") {
                    $sort = "u.real_name";
                    // Set order options for each sort type
                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=owner;order=ASC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                    } else {
                        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=owner;order=DESC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                    }
                } else {
                    if ($_GET['sort'] == "year") {
                        $sort = "v.made_year";
                        // Set order options for each sort type
                        if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                            $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=year;order=ASC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                        } else {
                            $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=year;order=DESC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                        }
                    } else {
                        if ($_GET['sort'] == "make") {
                            $sort = "mk.make";
                            // Set order options for each sort type
                            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=make;order=ASC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                            } else {
                                $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=make;order=DESC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                            }
                        } else {
                            if ($_GET['sort'] == "model") {
                                $sort = "md.model";
                                // Set order options for each sort type
                                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                    $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=model;order=ASC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                } else {
                                    $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=model;order=DESC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                }
                            } else {
                                if ($_GET['sort'] == "color") {
                                    $sort = "v.color";
                                    // Set order options for each sort type
                                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                        $context['sort']['byColor'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=color;order=ASC\">" . $txt['smfg_color'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                    } else {
                                        $context['sort']['byColor'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=color;order=DESC\">" . $txt['smfg_color'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                    }
                                } else {
                                    if ($_GET['sort'] == "views") {
                                        $sort = "v.views";
                                        // Set order options for each sort type
                                        if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                            $context['sort']['byViews'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=views;order=ASC\">" . $txt['smfg_views'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                        } else {
                                            $context['sort']['byViews'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=views;order=DESC\">" . $txt['smfg_views'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                        }
                                    } else {
                                        if ($_GET['sort'] == "mods") {
                                            $sort = "total_mods";
                                            // Set order options for each sort type
                                            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                                $context['sort']['byMods'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=mods;order=ASC\">" . $txt['smfg_mods'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                            } else {
                                                $context['sort']['byMods'] = "<a href=\"" . $scripturl . "?action=garage;sa=browse;sort=mods;order=DESC\">" . $txt['smfg_mods'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // Display as - Modifications
    if ($browse_type == 'modifications') {

        // Set the defaults and GET variables for sorting and order
        // Order options
        $order = 'DESC';
        if (!isset($_GET['order'])) {
            $_GET['order'] = "";
        }
        if ($_GET['order'] == "ASC") {
            $order = 'ASC';
        } else {
            if ($_GET['order'] == "DESC") {
                $order = 'DESC';
            }
        }

        // Create the default sort option links
        $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=modifications;sort=year\">" . $txt['smfg_year'] . "</a>";
        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=modifications;sort=make\">" . $txt['smfg_make'] . "</a>";
        $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=modifications;sort=model\">" . $txt['smfg_model'] . "</a>";
        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=modifications;sort=owner\">" . $txt['smfg_owner'] . "</a>";
        $context['sort']['byMod'] = "<a href=\"" . $scripturl . "?action=garage;sa=modifications;sort=modification\">" . $txt['smfg_modification'] . "</a>";
        $context['sort']['byUpdated'] = "<a href=\"" . $scripturl . "?action=garage;sa=modifications;sort=updated\">" . $txt['smfg_updated'] . "</a>";

        // Build sort option links with dynamic ordering
        $sort = "m.date_created";
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "";
        }
        if ($_GET['sort'] == "date_created") {
            $sort = "m.date_created";
        } else {
            if ($_GET['sort'] == "updated") {
                $sort = "m.date_updated";
                // Set order options for each sort type
                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                    $context['sort']['byUpdated'] = "<a href=\"" . $scripturl . "?action=garage;sa=modifications;sort=updated;order=ASC\">" . $txt['smfg_updated'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                } else {
                    $context['sort']['byUpdated'] = "<a href=\"" . $scripturl . "?action=garage;sa=modifications;sort=updated;order=DESC\">" . $txt['smfg_updated'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                }
            } else {
                if ($_GET['sort'] == "owner") {
                    $sort = "u.real_name";
                    // Set order options for each sort type
                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=modifications;sort=owner;order=ASC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                    } else {
                        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=modifications;sort=owner;order=DESC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                    }
                } else {
                    if ($_GET['sort'] == "year") {
                        $sort = "v.made_year";
                        // Set order options for each sort type
                        if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                            $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=modifications;sort=year;order=ASC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                        } else {
                            $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=modifications;sort=year;order=DESC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                        }
                    } else {
                        if ($_GET['sort'] == "make") {
                            $sort = "mk.make";
                            // Set order options for each sort type
                            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=modifications;sort=make;order=ASC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                            } else {
                                $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=modifications;sort=make;order=DESC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                            }
                        } else {
                            if ($_GET['sort'] == "model") {
                                $sort = "md.model";
                                // Set order options for each sort type
                                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                    $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=modifications;sort=model;order=ASC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                } else {
                                    $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=modifications;sort=model;order=DESC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                }
                            } else {
                                if ($_GET['sort'] == "modification") {
                                    $sort = "p.title";
                                    // Set order options for each sort type
                                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                        $context['sort']['byMod'] = "<a href=\"" . $scripturl . "?action=garage;sa=modifications;sort=modification;order=ASC\">" . $txt['smfg_modification'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                    } else {
                                        $context['sort']['byMod'] = "<a href=\"" . $scripturl . "?action=garage;sa=modifications;sort=modification;order=DESC\">" . $txt['smfg_modification'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

    }

    // Display as - Quartermiles
    if ($browse_type == 'quartermiles') {

        // Set the defaults and GET variables for sorting and order
        // Order options
        $order = 'ASC';
        if (!isset($_GET['order'])) {
            $_GET['order'] = "";
        }
        if ($_GET['order'] == "ASC") {
            $order = 'ASC';
        } else {
            if ($_GET['order'] == "DESC") {
                $order = 'DESC';
            }
        }

        // Create the default sort option links
        $context['sort']['byUsername'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=user\">Username</a>";
        $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=vehicle\">Vehicle</a>";
        $context['sort']['byRt'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=rt\">R/T</a>";
        $context['sort']['bySixty'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=sixty\">60 Foot</a>";
        $context['sort']['byThree'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=three\">330 Foot</a>";
        $context['sort']['byEighth'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=eighth\">1/8 Mile</a>";
        $context['sort']['byThou'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=thou\">1000 Foot</a>";
        $context['sort']['byQuart'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=quart\">1/4 Mile <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";

        // Set of get rid of the default sort image and link if they choose another sort option
        if (!isset($_GET['sort'])) {
            $context['sort']['byQuart'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=quart;order=DESC\">1/4 Mile <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
        } else {
            $context['sort']['byQuart'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=quart\">1/4 Mile</a>";
        }

        // Build sort option links with dynamic ordering
        $sort = "quart";
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "";
        }
        if ($_GET['sort'] == "user") {
            $sort = "real_name";
            // Set order options for each sort type
            if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                $context['sort']['byUsername'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=user;order=DESC\">Username <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
            } else {
                $context['sort']['byUsername'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=user;order=ASC\">Username <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
            }
        } else {
            if ($_GET['sort'] == "vehicle") {
                $sort = "vehicle";
                // Set order options for each sort type
                if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                    $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=vehicle;order=DESC\">Vehicle <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                } else {
                    $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=vehicle;order=ASC\">Vehicle <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                }
            } else {
                if ($_GET['sort'] == "rt") {
                    $sort = "rt";
                    // Set order options for each sort type
                    if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                        $context['sort']['byRt'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=rt;order=DESC\">R/T <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                    } else {
                        $context['sort']['byRt'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=rt;order=ASC\">R/T <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                    }
                } else {
                    if ($_GET['sort'] == "sixty") {
                        $sort = "sixty";
                        // Set order options for each sort type
                        if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                            $context['sort']['bySixty'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=sixty;order=DESC\">60 Foot <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                        } else {
                            $context['sort']['bySixty'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=sixty;order=ASC\">60 Foot <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                        }
                    } else {
                        if ($_GET['sort'] == "three") {
                            $sort = "three";
                            // Set order options for each sort type
                            if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                                $context['sort']['byThree'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=three;order=DESC\">330 Foot <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                            } else {
                                $context['sort']['byThree'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=three;order=ASC\">330 Foot <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                            }
                        } else {
                            if ($_GET['sort'] == "eighth") {
                                $sort = "eighth";
                                // Set order options for each sort type
                                if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                                    $context['sort']['byEighth'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=eighth;order=DESC\">1/8 Mile <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                } else {
                                    $context['sort']['byEighth'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=eighth;order=ASC\">1/8 Mile <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                }
                            } else {
                                if ($_GET['sort'] == "thou") {
                                    $sort = "thou";
                                    // Set order options for each sort type
                                    if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                                        $context['sort']['byThou'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=thou;order=DESC\">1000 Foot <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                    } else {
                                        $context['sort']['byThou'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=thou;order=ASC\">1000 Foot <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                    }
                                } else {
                                    if ($_GET['sort'] == "quart") {
                                        $sort = "quart";
                                        // Set order options for each sort type
                                        if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                                            $context['sort']['byQuart'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=quart;order=DESC\">1/4 Mile <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                        } else {
                                            $context['sort']['byQuart'] = "<a href=\"" . $scripturl . "?action=garage;sa=quartermiles;sort=quart;order=ASC\">1/4 Mile <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

    }

    // Display as - Dynoruns
    if ($browse_type == 'dynoruns') {

        // Set the defaults and GET variables for sorting and order
        // Order options
        $order = 'DESC';
        if (!isset($_GET['order'])) {
            $_GET['order'] = "";
        }
        if ($_GET['order'] == "ASC") {
            $order = 'ASC';
        } else {
            if ($_GET['order'] == "DESC") {
                $order = 'DESC';
            }
        }

        // Create the default sort option links
        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=user\">Username</a>";
        $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=vehicle\">Vehicle</a>";
        $context['sort']['byDynocenter'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=dynocenter\">Dynocenter</a>";
        $context['sort']['byBhp'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=bhp\">BHP</a>";
        $context['sort']['byTorque'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=torque\">Torque</a>";
        $context['sort']['byBoost'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=boost\">Boost</a>";
        $context['sort']['byNitrous'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=nitrous\">Nitrous</a>";
        $context['sort']['byPeakpoint'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=peak\">Peakpoint</a>";

        // Set or get rid of the default sort image and link if they choose another sort option
        if (!isset($_GET['sort'])) {
            $context['sort']['byBhp'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=bhp;order=ASC\">BHP <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
        } else {
            $context['sort']['byBhp'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=bhp\">BHP</a>";
        }

        // Build sort option links with dynamic ordering
        $sort = "d.bhp";
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "";
        }
        if ($_GET['sort'] == "user") {
            $sort = "u.real_name";
            // Set order options for each sort type
            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=user;order=ASC\">Username <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
            } else {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=user;order=DESC\">Username <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
            }
        } else {
            if ($_GET['sort'] == "vehicle") {
                $sort = "vehicle";
                // Set order options for each sort type
                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                    $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=vehicle;order=ASC\">Vehicle <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                } else {
                    $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=vehicle;order=DESC\">Vehicle <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                }
            } else {
                if ($_GET['sort'] == "dynocenter") {
                    $sort = "b.title";
                    // Set order options for each sort type
                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                        $context['sort']['byDynocenter'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=dynocenter;order=ASC\">Dynocenter <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                    } else {
                        $context['sort']['byDynocenter'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=dynocenter;order=DESC\">Dynocenter <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                    }
                } else {
                    if ($_GET['sort'] == "bhp") {
                        $sort = "d.bhp";
                        // Set order options for each sort type
                        if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                            $context['sort']['byBhp'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=bhp;order=ASC\">BHP <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                        } else {
                            $context['sort']['byBhp'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=bhp;order=DESC\">BHP <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                        }
                    } else {
                        if ($_GET['sort'] == "torque") {
                            $sort = "d.torque";
                            // Set order options for each sort type
                            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                $context['sort']['byTorque'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=torque;order=ASC\">Torque <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                            } else {
                                $context['sort']['byTorque'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=torque;order=DESC\">Torque <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                            }
                        } else {
                            if ($_GET['sort'] == "boost") {
                                $sort = "d.boost";
                                // Set order options for each sort type
                                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                    $context['sort']['byBoost'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=boost;order=ASC\">Boost <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                } else {
                                    $context['sort']['byBoost'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=boost;order=DESC\">Boost <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                }
                            } else {
                                if ($_GET['sort'] == "nitrous") {
                                    $sort = "d.nitrous";
                                    // Set order options for each sort type
                                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                        $context['sort']['byNitrous'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=nitrous;order=ASC\">Nitrous <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                    } else {
                                        $context['sort']['byNitrous'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=nitrous;order=DESC\">Nitrous <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                    }
                                } else {
                                    if ($_GET['sort'] == "peak") {
                                        $sort = "d.peakpoint";
                                        // Set order options for each sort type
                                        if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                            $context['sort']['byPeakpoint'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=peak;order=ASC\">Peakpoint <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                        } else {
                                            $context['sort']['byPeakpoint'] = "<a href=\"" . $scripturl . "?action=garage;sa=dynoruns;sort=peak;order=DESC\">Peakpoint <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

    }

    // Display as - Laps
    if ($browse_type == 'laps') {

        // Set the defaults and GET variables for sorting and order
        // Order options
        $order = 'ASC';
        if (!isset($_GET['order'])) {
            $_GET['order'] = "";
        }
        if ($_GET['order'] == "ASC") {
            $order = 'ASC';
        } else {
            if ($_GET['order'] == "DESC") {
                $order = 'DESC';
            }
        }

        // Create the default sort option links
        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=user\">Username</a>";
        $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=vehicle\">Vehicle</a>";
        $context['sort']['byTrack'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=track\">Track</a>";
        $context['sort']['byCondition'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=condition\">Condition</a>";
        $context['sort']['byType'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=type\">Type</a>";
        $context['sort']['byTime'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=time\">Time (M:S:MS)</a>";

        // Set or Get rid of the default sort image and link if they choose another sort option
        if (!isset($_GET['sort'])) {
            $context['sort']['byTime'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=time;order=DESC\">Time (M:S:MS) <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
        } else {
            $context['sort']['byTime'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=time\">Time (M:S:MS)</a>";
        }

        // Build sort option links with dynamic ordering
        $sort = "t.title " . $order . ", l.minute " . $order . ", l.second " . $order . ", l.millisecond";
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "";
        }
        if ($_GET['sort'] == "user") {
            $sort = "u.real_name";
            // Set order options for each sort type
            if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=user;order=DESC\">Username <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
            } else {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=user;order=ASC\">Username <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
            }
        } else {
            if ($_GET['sort'] == "vehicle") {
                $sort = "vehicle";
                // Set order options for each sort type
                if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                    $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=vehicle;order=DESC\">Vehicle <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                } else {
                    $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=vehicle;order=ASC\">Vehicle <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                }
            } else {
                if ($_GET['sort'] == "track") {
                    $sort = "t.title";
                    // Set order options for each sort type
                    if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                        $context['sort']['byTrack'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=track;order=DESC\">Track <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                    } else {
                        $context['sort']['byTrack'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=track;order=ASC\">Track <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                    }
                } else {
                    if ($_GET['sort'] == "condition") {
                        $sort = "tc.title";
                        // Set order options for each sort type
                        if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                            $context['sort']['byCondition'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=condition;order=DESC\">Condition <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                        } else {
                            $context['sort']['byCondition'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=condition;order=ASC\">Condition <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                        }
                    } else {
                        if ($_GET['sort'] == "type") {
                            $sort = "lt.title";
                            // Set order options for each sort type
                            if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                                $context['sort']['byType'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=type;order=DESC\">Type <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                            } else {
                                $context['sort']['byType'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=type;order=ASC\">Type <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                            }
                        } else {
                            if ($_GET['sort'] == "time") {
                                $sort = "time";
                                // Set order options for each sort type
                                if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                                    $context['sort']['byTime'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=time;order=DESC\">Time (M:S:MS) <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                } else {
                                    $context['sort']['byTime'] = "<a href=\"" . $scripturl . "?action=garage;sa=laptimes;sort=time;order=ASC\">Time (M:S:MS) <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                }
                            }
                        }
                    }
                }
            }
        }

    }

    // Display as - Most Modified
    if ($browse_type == 'mostmodified') {

        // Set the defaults and GET variables for sorting and order
        // Order options
        $order = 'DESC';
        if (!isset($_GET['order'])) {
            $_GET['order'] = "";
        }
        if ($_GET['order'] == "ASC") {
            $order = 'ASC';
        } else {
            if ($_GET['order'] == "DESC") {
                $order = 'DESC';
            }
        }

        // Create the default sort option links
        $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostmodified;sort=year\">" . $txt['smfg_year'] . "</a>";
        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostmodified;sort=make\">" . $txt['smfg_make'] . "</a>";
        $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostmodified;sort=model\">" . $txt['smfg_model'] . "</a>";
        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostmodified;sort=owner\">" . $txt['smfg_owner'] . "</a>";
        $context['sort']['byMods'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostmodified;sort=mods\">" . $txt['smfg_mods'] . "</a>";

        // Build sort option links with dynamic ordering
        $sort = "total_mods";
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "";
        }
        if ($_GET['sort'] == "date_created") {
            $sort = "m.date_created";
        } else {
            if ($_GET['sort'] == "updated") {
                $sort = "m.date_updated";
                // Set order options for each sort type
                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                    $context['sort']['byUpdated'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostmodified;sort=updated;order=ASC\">" . $txt['smfg_updated'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                } else {
                    $context['sort']['byUpdated'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostmodified;sort=updated;order=DESC\">" . $txt['smfg_updated'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                }
            } else {
                if ($_GET['sort'] == "owner") {
                    $sort = "u.real_name";
                    // Set order options for each sort type
                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostmodified;sort=owner;order=ASC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                    } else {
                        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostmodified;sort=owner;order=DESC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                    }
                } else {
                    if ($_GET['sort'] == "year") {
                        $sort = "v.made_year";
                        // Set order options for each sort type
                        if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                            $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostmodified;sort=year;order=ASC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                        } else {
                            $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostmodified;sort=year;order=DESC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                        }
                    } else {
                        if ($_GET['sort'] == "make") {
                            $sort = "mk.make";
                            // Set order options for each sort type
                            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostmodified;sort=make;order=ASC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                            } else {
                                $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostmodified;sort=make;order=DESC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                            }
                        } else {
                            if ($_GET['sort'] == "model") {
                                $sort = "md.model";
                                // Set order options for each sort type
                                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                    $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostmodified;sort=model;order=ASC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                } else {
                                    $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostmodified;sort=model;order=DESC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                }
                            } else {
                                if ($_GET['sort'] == "mods") {
                                    $sort = "total_mods";
                                    // Set order options for each sort type
                                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                        $context['sort']['byMods'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostmodified;sort=mods;order=ASC\">" . $txt['smfg_mods'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                    } else {
                                        $context['sort']['byMods'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostmodified;sort=mods;order=DESC\">" . $txt['smfg_mods'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

    }

    // Display as - Most Viewed
    if ($browse_type == 'mostviewed') {

        // Set the defaults and GET variables for sorting and order
        // Order options
        $order = 'DESC';
        if (!isset($_GET['order'])) {
            $_GET['order'] = "";
        }
        if ($_GET['order'] == "ASC") {
            $order = 'ASC';
        } else {
            if ($_GET['order'] == "DESC") {
                $order = 'DESC';
            }
        }

        // Create the default sort option links
        $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostviewed;sort=year\">" . $txt['smfg_year'] . "</a>";
        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostviewed;sort=make\">" . $txt['smfg_make'] . "</a>";
        $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostviewed;sort=model\">" . $txt['smfg_model'] . "</a>";
        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostviewed;sort=owner\">" . $txt['smfg_owner'] . "</a>";
        $context['sort']['byViews'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostviewed;sort=views\">" . $txt['smfg_views'] . "</a>";

        // Build sort option links with dynamic ordering
        $sort = "v.views";
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "";
        }
        if ($_GET['sort'] == "owner") {
            $sort = "u.real_name";
            // Set order options for each sort type
            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostviewed;sort=owner;order=ASC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
            } else {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostviewed;sort=owner;order=DESC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
            }
        } else {
            if ($_GET['sort'] == "year") {
                $sort = "v.made_year";
                // Set order options for each sort type
                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                    $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostviewed;sort=year;order=ASC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                } else {
                    $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostviewed;sort=year;order=DESC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                }
            } else {
                if ($_GET['sort'] == "make") {
                    $sort = "mk.make";
                    // Set order options for each sort type
                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostviewed;sort=make;order=ASC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                    } else {
                        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostviewed;sort=make;order=DESC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                    }
                } else {
                    if ($_GET['sort'] == "model") {
                        $sort = "md.model";
                        // Set order options for each sort type
                        if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                            $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostviewed;sort=model;order=ASC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                        } else {
                            $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostviewed;sort=model;order=DESC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                        }
                    } else {
                        if ($_GET['sort'] == "views") {
                            $sort = "v.views";
                            // Set order options for each sort type
                            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                $context['sort']['byViews'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostviewed;sort=views;order=ASC\">" . $txt['smfg_views'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                            } else {
                                $context['sort']['byViews'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostviewed;sort=views;order=DESC\">" . $txt['smfg_views'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                            }
                        }
                    }
                }
            }
        }

    }

    // Display as - Latest Service
    if ($browse_type == 'latestservice') {

        // Set the defaults and GET variables for sorting and order
        // Order options
        $order = 'DESC';
        if (!isset($_GET['order'])) {
            $_GET['order'] = "";
        }
        if ($_GET['order'] == "ASC") {
            $order = 'ASC';
        } else {
            if ($_GET['order'] == "DESC") {
                $order = 'DESC';
            }
        }

        // Create the default sort option links
        $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestservice;sort=year\">" . $txt['smfg_year'] . "</a>";
        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestservice;sort=make\">" . $txt['smfg_make'] . "</a>";
        $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestservice;sort=model\">" . $txt['smfg_model'] . "</a>";
        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestservice;sort=owner\">" . $txt['smfg_owner'] . "</a>";
        $context['sort']['byType'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestservice;sort=type\">" . $txt['smfg_type'] . "</a>";
        $context['sort']['byCreated'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestservice;sort=created\">" . $txt['smfg_created'] . "</a>";

        // Build sort option links with dynamic ordering
        $sort = "sh.id";
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "";
        }
        if ($_GET['sort'] == "owner") {
            $sort = "u.real_name";
            // Set order options for each sort type
            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestservice;sort=owner;order=ASC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
            } else {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestservice;sort=owner;order=DESC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
            }
        } else {
            if ($_GET['sort'] == "year") {
                $sort = "v.made_year";
                // Set order options for each sort type
                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                    $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestservice;sort=year;order=ASC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                } else {
                    $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestservice;sort=year;order=DESC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                }
            } else {
                if ($_GET['sort'] == "make") {
                    $sort = "mk.make";
                    // Set order options for each sort type
                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestservice;sort=make;order=ASC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                    } else {
                        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestservice;sort=make;order=DESC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                    }
                } else {
                    if ($_GET['sort'] == "model") {
                        $sort = "md.model";
                        // Set order options for each sort type
                        if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                            $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestservice;sort=model;order=ASC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                        } else {
                            $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestservice;sort=model;order=DESC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                        }
                    } else {
                        if ($_GET['sort'] == "type") {
                            $sort = "t.title";
                            // Set order options for each sort type
                            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                $context['sort']['byType'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestservice;sort=type;order=ASC\">" . $txt['smfg_type'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                            } else {
                                $context['sort']['byType'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestservice;sort=type;order=DESC\">" . $txt['smfg_type'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                            }
                        } else {
                            if ($_GET['sort'] == "created") {
                                $sort = "sh.date_created";
                                // Set order options for each sort type
                                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                    $context['sort']['byCreated'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestservice;sort=created;order=ASC\">" . $txt['smfg_created'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                } else {
                                    $context['sort']['byCreated'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestservice;sort=created;order=DESC\">" . $txt['smfg_created'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                }
                            }
                        }
                    }
                }
            }
        }

    }

    // Display as - Top Rated
    if ($browse_type == 'toprated') {

        // Set the defaults and GET variables for sorting and order
        // Order options
        $order = 'DESC';
        if (!isset($_GET['order'])) {
            $_GET['order'] = "";
        }
        if ($_GET['order'] == "ASC") {
            $order = 'ASC';
        } else {
            if ($_GET['order'] == "DESC") {
                $order = 'DESC';
            }
        }

        // Create the default sort option links
        $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=toprated;sort=year\">" . $txt['smfg_year'] . "</a>";
        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=toprated;sort=make\">" . $txt['smfg_make'] . "</a>";
        $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=toprated;sort=model\">" . $txt['smfg_model'] . "</a>";
        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=toprated;sort=owner\">" . $txt['smfg_owner'] . "</a>";
        $context['sort']['byRating'] = "<a href=\"" . $scripturl . "?action=garage;sa=toprated;sort=rating\">" . $txt['smfg_rating'] . "</a>";

        // Build sort option links with dynamic ordering
        $sort = "rating";
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "";
        }
        if ($_GET['sort'] == "owner") {
            $sort = "u.real_name";
            // Set order options for each sort type
            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=toprated;sort=owner;order=ASC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
            } else {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=toprated;sort=owner;order=DESC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
            }
        } else {
            if ($_GET['sort'] == "year") {
                $sort = "v.made_year";
                // Set order options for each sort type
                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                    $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=toprated;sort=year;order=ASC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                } else {
                    $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=toprated;sort=year;order=DESC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                }
            } else {
                if ($_GET['sort'] == "make") {
                    $sort = "mk.make";
                    // Set order options for each sort type
                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=toprated;sort=make;order=ASC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                    } else {
                        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=toprated;sort=make;order=DESC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                    }
                } else {
                    if ($_GET['sort'] == "model") {
                        $sort = "md.model";
                        // Set order options for each sort type
                        if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                            $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=toprated;sort=model;order=ASC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                        } else {
                            $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=toprated;sort=model;order=DESC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                        }
                    } else {
                        if ($_GET['sort'] == "rating") {
                            $sort = "rating";
                            // Set order options for each sort type
                            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                $context['sort']['byRating'] = "<a href=\"" . $scripturl . "?action=garage;sa=toprated;sort=rating;order=ASC\">" . $txt['smfg_rating'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                            } else {
                                $context['sort']['byRating'] = "<a href=\"" . $scripturl . "?action=garage;sa=toprated;sort=rating;order=DESC\">" . $txt['smfg_rating'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                            }
                        }
                    }
                }
            }
        }

    }

    // Display as - Most Spent
    if ($browse_type == 'mostspent') {

        // Set the defaults and GET variables for sorting and order
        // Order options
        $order = 'DESC';
        if (!isset($_GET['order'])) {
            $_GET['order'] = "";
        }
        if ($_GET['order'] == "ASC") {
            $order = 'ASC';
        } else {
            if ($_GET['order'] == "DESC") {
                $order = 'DESC';
            }
        }

        // Create the default sort option links
        $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostspent;sort=year\">" . $txt['smfg_year'] . "</a>";
        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostspent;sort=make\">" . $txt['smfg_make'] . "</a>";
        $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostspent;sort=model\">" . $txt['smfg_model'] . "</a>";
        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostspent;sort=owner\">" . $txt['smfg_owner'] . "</a>";
        $context['sort']['byTotalSpent'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostspent;sort=totalspent\">" . $txt['smfg_total_spent'] . "</a>";

        // Build sort option links with dynamic ordering
        $sort = "total_spent";
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "";
        }
        if ($_GET['sort'] == "owner") {
            $sort = "u.real_name";
            // Set order options for each sort type
            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostspent;sort=owner;order=ASC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
            } else {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostspent;sort=owner;order=DESC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
            }
        } else {
            if ($_GET['sort'] == "year") {
                $sort = "v.made_year";
                // Set order options for each sort type
                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                    $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostspent;sort=year;order=ASC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                } else {
                    $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostspent;sort=year;order=DESC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                }
            } else {
                if ($_GET['sort'] == "make") {
                    $sort = "mk.make";
                    // Set order options for each sort type
                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostspent;sort=make;order=ASC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                    } else {
                        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostspent;sort=make;order=DESC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                    }
                } else {
                    if ($_GET['sort'] == "model") {
                        $sort = "md.model";
                        // Set order options for each sort type
                        if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                            $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostspent;sort=model;order=ASC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                        } else {
                            $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostspent;sort=model;order=DESC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                        }
                    } else {
                        if ($_GET['sort'] == "totalspent") {
                            $sort = "total_spent";
                            // Set order options for each sort type
                            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                $context['sort']['byTotalSpent'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostspent;sort=totalspent;order=ASC\">" . $txt['smfg_total_spent'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                            } else {
                                $context['sort']['byTotalSpent'] = "<a href=\"" . $scripturl . "?action=garage;sa=mostspent;sort=totalspent;order=DESC\">" . $txt['smfg_total_spent'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                            }
                        }
                    }
                }
            }
        }

    }

    // Display as - Latest Blog
    if ($browse_type == 'latestblog') {

        // Set the defaults and GET variables for sorting and order
        // Order options
        $order = 'DESC';
        if (!isset($_GET['order'])) {
            $_GET['order'] = "";
        }
        if ($_GET['order'] == "ASC") {
            $order = 'ASC';
        } else {
            if ($_GET['order'] == "DESC") {
                $order = 'DESC';
            }
        }

        // Create the default sort option links
        $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestblog;sort=year\">" . $txt['smfg_year'] . "</a>";
        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestblog;sort=make\">" . $txt['smfg_make'] . "</a>";
        $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestblog;sort=model\">" . $txt['smfg_model'] . "</a>";
        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestblog;sort=owner\">" . $txt['smfg_owner'] . "</a>";
        $context['sort']['byTitle'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestblog;sort=title\">" . $txt['smfg_blog_title'] . "</a>";
        $context['sort']['byPosted'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestblog;sort=posted\">" . $txt['smfg_posted_date'] . "</a>";

        // Build sort option links with dynamic ordering
        $sort = "b.post_date";
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "";
        }
        if ($_GET['sort'] == "owner") {
            $sort = "u.real_name";
            // Set order options for each sort type
            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestblog;sort=owner;order=ASC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
            } else {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestblog;sort=owner;order=DESC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
            }
        } else {
            if ($_GET['sort'] == "year") {
                $sort = "v.made_year";
                // Set order options for each sort type
                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                    $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestblog;sort=year;order=ASC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                } else {
                    $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestblog;sort=year;order=DESC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                }
            } else {
                if ($_GET['sort'] == "make") {
                    $sort = "mk.make";
                    // Set order options for each sort type
                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestblog;sort=make;order=ASC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                    } else {
                        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestblog;sort=make;order=DESC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                    }
                } else {
                    if ($_GET['sort'] == "model") {
                        $sort = "md.model";
                        // Set order options for each sort type
                        if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                            $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestblog;sort=model;order=ASC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                        } else {
                            $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestblog;sort=model;order=DESC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                        }
                    } else {
                        if ($_GET['sort'] == "title") {
                            $sort = "b.blog_title";
                            // Set order options for each sort type
                            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                $context['sort']['byTitle'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestblog;sort=title;order=ASC\">" . $txt['smfg_blog_title'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                            } else {
                                $context['sort']['byTitle'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestblog;sort=title;order=DESC\">" . $txt['smfg_blog_title'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                            }
                        } else {
                            if ($_GET['sort'] == "posted") {
                                $sort = "b.post_date";
                                // Set order options for each sort type
                                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                    $context['sort']['byPosted'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestblog;sort=posted;order=ASC\">" . $txt['smfg_posted_date'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                } else {
                                    $context['sort']['byPosted'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestblog;sort=posted;order=DESC\">" . $txt['smfg_posted_date'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                }
                            }
                        }
                    }
                }
            }
        }

    }

    // Display as - Latest Blog
    if ($browse_type == 'latestvideo') {

        // Set the defaults and GET variables for sorting and order
        // Order options
        $order = 'DESC';
        if (!isset($_GET['order'])) {
            $_GET['order'] = "";
        }
        if ($_GET['order'] == "ASC") {
            $order = 'ASC';
        } else {
            if ($_GET['order'] == "DESC") {
                $order = 'DESC';
            }
        }

        // Create the default sort option links
        $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestvideo;sort=year\">" . $txt['smfg_year'] . "</a>";
        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestvideo;sort=make\">" . $txt['smfg_make'] . "</a>";
        $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestvideo;sort=model\">" . $txt['smfg_model'] . "</a>";
        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestvideo;sort=owner\">" . $txt['smfg_owner'] . "</a>";
        $context['sort']['byTitle'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestvideo;sort=title\">" . $txt['smfg_video_title'] . "</a>";

        // Build sort option links with dynamic ordering
        $sort = "b.id";
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "";
        }
        if ($_GET['sort'] == "owner") {
            $sort = "u.real_name";
            // Set order options for each sort type
            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestvideo;sort=owner;order=ASC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
            } else {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestvideo;sort=owner;order=DESC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
            }
        } else {
            if ($_GET['sort'] == "year") {
                $sort = "v.made_year";
                // Set order options for each sort type
                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                    $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestvideo;sort=year;order=ASC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                } else {
                    $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestvideo;sort=year;order=DESC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                }
            } else {
                if ($_GET['sort'] == "make") {
                    $sort = "mk.make";
                    // Set order options for each sort type
                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestvideo;sort=make;order=ASC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                    } else {
                        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestvideo;sort=make;order=DESC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                    }
                } else {
                    if ($_GET['sort'] == "model") {
                        $sort = "md.model";
                        // Set order options for each sort type
                        if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                            $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestvideo;sort=model;order=ASC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                        } else {
                            $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestvideo;sort=model;order=DESC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                        }
                    } else {
                        if ($_GET['sort'] == "title") {
                            $sort = "b.title";
                            // Set order options for each sort type
                            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                $context['sort']['byTitle'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestvideo;sort=title;order=ASC\">" . $txt['smfg_video_title'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                            } else {
                                $context['sort']['byTitle'] = "<a href=\"" . $scripturl . "?action=garage;sa=latestvideo;sort=title;order=DESC\">" . $txt['smfg_video_title'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                            }
                        }
                    }
                }
            }
        }

    }

    // Assign the tables and selects needed
    $browse_selects = "";
    if ($browse_type == "vehicles") {

        $browse_selects .= 'v.id, v.made_year, mk.make, md.model, v.color, v.user_id, u.real_name, v.views, COUNT(m.id) as total_mods, v.date_updated';
        $browse_tables = '{db_prefix}garage_vehicles AS v LEFT OUTER JOIN {db_prefix}garage_modifications AS m ON v.id = m.vehicle_id, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
        WHERE v.user_id = u.id_member AND v.make_id = mk.id AND v.model_id = md.id AND mk.pending != "1" AND md.pending != "1" AND v.pending != "1"';

    } else {
        if ($browse_type == "modifications") {

            $browse_selects .= 'v.id, v.made_year, mk.make, md.model, v.user_id, u.real_name, m.id, p.title, b.title, c.title, m.date_updated';
            $browse_tables = '{db_prefix}garage_modifications AS m, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_products AS p, {db_prefix}garage_business AS b, {db_prefix}garage_categories AS c, {db_prefix}members AS u 
        WHERE v.user_id = u.id_member AND v.make_id = mk.id AND v.model_id = md.id AND m.product_id = p.id AND m.vehicle_id = v.id AND p.business_id = b.id AND m.category_id = c.id AND mk.pending != "1" AND md.pending != "1" AND v.pending != "1" AND m.pending != "1"';

        } else {
            if ($browse_type == "quartermiles") {

                $browse_selects .= 'q.id, v.id, CONCAT_WS( " ", v.made_year, mk.make, md.model) AS vehicle, v.user_id, u.real_name, q.rt, q.sixty, q.three, q.eighth, q.eighthmph, q.thou, q.quart, q.quartmph';
                $browse_tables = '{db_prefix}garage_quartermiles AS q, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
        WHERE u.id_member = v.user_id AND v.make_id = mk.id AND v.model_id = md.id AND q.vehicle_id = v.id AND mk.pending != "1" AND md.pending != "1" AND v.pending != "1"';

            } else {
                if ($browse_type == "dynoruns") {

                    $browse_selects .= 'v.id, d.id, CONCAT_WS( " ", v.made_year, mk.make, md.model) AS vehicle, v.user_id, u.real_name, d.bhp, d.bhp_unit, d.torque, d.torque_unit, d.boost, d.boost_unit, d.nitrous, d.peakpoint, b.title, b.id';
                    $browse_tables = '{db_prefix}garage_dynoruns AS d LEFT OUTER JOIN {db_prefix}garage_business AS b ON b.id = d.dynocenter_id, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u 
        WHERE u.id_member = v.user_id AND v.make_id = mk.id AND v.model_id = md.id AND d.vehicle_id = v.id AND mk.pending != "1" AND md.pending != "1" AND v.pending != "1" AND b.pending != "1"';

                } else {
                    if ($browse_type == "laps") {

                        $browse_selects .= 'v.id, CONCAT_WS( " ", v.made_year, mk.make, md.model) AS vehicle, v.user_id, u.real_name, l.track_id, t.title, tc.title, lt.title, l.id, CONCAT_WS(":",l.minute, l.second, l.millisecond) AS time';
                        $browse_tables = '{db_prefix}garage_laps AS l, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u, {db_prefix}garage_tracks AS t, {db_prefix}garage_track_conditions AS tc, {db_prefix}garage_lap_types AS lt
        WHERE u.id_member = v.user_id AND v.make_id = mk.id AND v.model_id = md.id AND l.vehicle_id = v.id AND l.track_id = t.id AND l.condition_id = tc.id AND l.type_id = lt.id AND mk.pending != "1" AND md.pending != "1" AND v.pending != "1" AND t.pending != "1"';

                    } else {
                        if ($browse_type == "mostmodified") {

                            $browse_selects .= 'v.id, COUNT( m.id ) AS total_mods, v.user_id, v.made_year, mk.make, md.model, u.real_name';
                            $browse_tables = '{db_prefix}garage_vehicles AS v LEFT OUTER JOIN {db_prefix}garage_modifications AS m ON v.id = m.vehicle_id, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_products AS p, {db_prefix}garage_business AS b, {db_prefix}members AS u
        WHERE v.user_id = u.id_member AND v.make_id = mk.id AND v.model_id = md.id AND m.product_id = p.id AND p.business_id = b.id AND mk.pending != "1" AND md.pending != "1" AND v.pending != "1" AND m.pending != "1" AND p.pending != "1" AND b.pending != "1"';

                        } else {
                            if ($browse_type == "mostviewed") {

                                $browse_selects .= 'v.id, v.made_year, mk.make, md.model, v.views, v.user_id, u.real_name';
                                $browse_tables = '{db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
        WHERE v.make_id = mk.id AND v.model_id = md.id AND mk.pending != "1" AND md.pending != "1" AND v.pending != "1" AND v.user_id = u.id_member';

                            } else {
                                if ($browse_type == "latestservice") {

                                    $browse_selects .= 'sh.vehicle_id, v.made_year, mk.make, md.model, v.user_id, u.real_name, t.title, sh.date_created';
                                    $browse_tables = '{db_prefix}garage_service_history AS sh, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u, {db_prefix}garage_service_types AS t
        WHERE sh.vehicle_id = v.id AND v.make_id = mk.id AND v.model_id = md.id AND mk.pending != "1" AND md.pending != "1" AND v.pending != "1" AND v.user_id = u.id_member AND sh.type_id = t.id';

                                } else {
                                    if ($browse_type == "toprated") {

                                        if ($smfgSettings['rating_system'] == 0) {
                                            $ratingfunc = "SUM";
                                        } else {
                                            if ($smfgSettings['rating_system'] == 1) {
                                                $ratingfunc = "AVG";
                                            }
                                        }

                                        $browse_selects .= 'r.vehicle_id, ' . $ratingfunc . '( r.rating ) AS rating, COUNT( r.id ) * 10 AS poss_rating, v.made_year, mk.make, md.model, v.user_id, u.real_name';
                                        $browse_tables = '{db_prefix}garage_ratings AS r, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
        WHERE r.vehicle_id = v.id AND v.make_id = mk.id AND v.model_id = md.id AND mk.pending != "1" AND md.pending != "1" AND v.pending != "1" AND v.user_id = u.id_member';

                                    } else {
                                        if ($browse_type == "mostspent") {

                                            $browse_selects .= 'v.id, IFNULL(m.total_mods,0) + IFNULL(s.total_service,0) AS total_spent, v.made_year, mk.make, md.model, v.user_id, u.real_name, c.title AS currency';
                                            $browse_tables = '{db_prefix}garage_vehicles AS v LEFT OUTER JOIN ( SELECT vehicle_id, SUM(price) + SUM(install_price) AS total_mods FROM {db_prefix}garage_modifications AS m1, {db_prefix}garage_business AS b, {db_prefix}garage_products AS p WHERE m1.manufacturer_id = b.id AND m1.product_id = p.id AND b.pending != "1" AND m1.pending != "1" AND p.pending != "1" GROUP BY vehicle_id) AS m ON v.id = m.vehicle_id LEFT OUTER JOIN ( SELECT vehicle_id, SUM(price) AS total_service FROM {db_prefix}garage_service_history AS s1, {db_prefix}garage_business AS b1 WHERE s1.garage_id = b1.id AND b1.pending != "1" GROUP BY vehicle_id) AS s ON v.id = s.vehicle_id, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_currency AS c, {db_prefix}members AS u
        WHERE v.make_id = mk.id AND v.model_id = md.id AND mk.pending != "1" AND md.pending != "1" AND v.pending != "1" AND v.user_id = u.id_member AND v.currency = c.id';

                                        } else {
                                            if ($browse_type == "latestblog") {

                                                $browse_selects .= 'b.vehicle_id, b.blog_title, v.made_year, mk.make, md.model, v.user_id, u.real_name, b.post_date';
                                                $browse_tables = '{db_prefix}garage_blog AS b, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
        WHERE b.vehicle_id = v.id AND v.make_id = mk.id AND v.model_id = md.id AND mk.pending != "1" AND md.pending != "1" AND v.pending != "1" AND v.user_id = u.id_member';

                                            } else {
                                                if ($browse_type == "latestvideo") {

                                                    $browse_selects .= 'b.id, b.vehicle_id, b.title, v.made_year, mk.make, md.model, v.user_id, u.real_name, b2.type, b2.type_id, b.url, b.video_desc';
                                                    $browse_tables = '{db_prefix}garage_video AS b, {db_prefix}garage_video_gallery AS b2, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
        WHERE b.vehicle_id = v.id AND b2.video_id = b.id AND v.make_id = mk.id AND v.model_id = md.id AND mk.pending != "1" AND md.pending != "1" AND v.pending != "1" AND v.user_id = u.id_member';

                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // Assign any group by statements after joins if needed
    if ($browse_type == "vehicles") {
        $group_by = 'GROUP BY v.id, v.made_year, mk.make, md.model, v.color, v.user_id, u.real_name, v.views, v.date_updated';
    } else if ($browse_type == "mostmodified") {
        $group_by = 'GROUP BY v.id, v.user_id, v.made_year, mk.make, md.model, u.real_name';
    } else if ($browse_type == "toprated") {
        $group_by = 'GROUP BY r.vehicle_id, v.made_year, mk.make, md.model, v.user_id, u.real_name';
    } else if ($browse_type == "mostspent") {
        $group_by = 'GROUP BY v.id, IFNULL(m.total_mods,0) + IFNULL(s.total_service,0), v.made_year, mk.make, md.model, v.user_id, u.real_name, c.title';
    } else {
        $group_by = '';
    }

    // Disable query check?
    if ($browse_type == "mostspent") {

        // *************************************************************
        // WARNING: The query check is being disabled to allow for the following subselect.
        // It is imperative this is turned back on for security reasons.
        // *************************************************************
        $modSettings['disableQueryCheck'] = 1;
        // *************************************************************

    }

    // Get the total number of results for pagination
    $request = $smcFunc['db_query']('', '
        SELECT ' . $browse_selects . '
        FROM ' . $browse_tables . ' ' . $group_by . '
        ORDER BY ' . $sort . ' ' . $order,
        array(// no values
        )
    );
    $context['total'] = $smcFunc['db_num_rows']($request);

    // Set pagination variables
    $context['display'] = $smfgSettings['cars_per_page'];
    $context['start'] = $_REQUEST['start'] + 1;
    $context['end'] = min($_REQUEST['start'] + $context['display'], $context['total']);

    if ($browse_type == "vehicles") {
        $context['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=browse' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : ''),
            $_REQUEST['start'], $context['total'], $context['display']);
        $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_browse'] . ' &gt; ' . $txt['smfg_viewing_vehicles'] . ' ' . $context['start'] . ' ' . $txt['smfg_to'] . ' ' . $context['end'];
        $context['linktree'][] = array(
            'url' => $scripturl . '?action=garage;sa=browse' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : '') . (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0'),
            'name' => $txt['smfg_viewing_vehicles'] . ' ' . ($context['total'] == 0 ? $context['total'] : $context['start']) . ' ' . $txt['smfg_to'] . ' ' . $context['end'] . '',
            'extra_after' => ' (' . $txt['smfg_of'] . ' ' . $context['total'] . ' ' . $txt['smfg_total_vehicles'] . ')'
        );
        $context['browse_type_total'] = $txt['smfg_vehicles'];
    } else {
        if ($browse_type == 'quartermiles') {
            $context['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=quartermiles' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : ''),
                $_REQUEST['start'], $context['total'], $context['display']);
            $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_quartermiles'] . ' &gt; ' . $txt['smfg_viewing_quartermiles'] . ' ' . $context['start'] . ' ' . $txt['smfg_to'] . ' ' . $context['end'];
            $context['linktree'][] = array(
                'url' => $scripturl . '?action=garage;sa=quartermiles' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : '') . (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0'),
                'name' => $txt['smfg_viewing_quartermiles'] . ' ' . ($context['total'] == 0 ? $context['total'] : $context['start']) . ' ' . $txt['smfg_to'] . ' ' . $context['end'] . '',
                'extra_after' => ' (' . $txt['smfg_of'] . ' ' . $context['total'] . ' ' . $txt['smfg_total_quartermiles'] . ')'
            );
            $context['browse_type_total'] = $txt['smfg_quartermiles_lower'];
        } else {
            if ($browse_type == 'dynoruns') {
                $context['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=dynoruns' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : ''),
                    $_REQUEST['start'], $context['total'], $context['display']);
                $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_dynoruns'] . ' &gt; ' . $txt['smfg_viewing_dynoruns'] . ' ' . $context['start'] . ' ' . $txt['smfg_to'] . ' ' . $context['end'];
                $context['linktree'][] = array(
                    'url' => $scripturl . '?action=garage;sa=dynoruns' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : '') . (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0'),
                    'name' => $txt['smfg_viewing_dynoruns'] . ' ' . ($context['total'] == 0 ? $context['total'] : $context['start']) . ' ' . $txt['smfg_to'] . ' ' . $context['end'] . '',
                    'extra_after' => ' (' . $txt['smfg_of'] . ' ' . $context['total'] . ' ' . $txt['smfg_total_dynoruns'] . ')'
                );
                $context['browse_type_total'] = $txt['smfg_dynoruns_lower'];
            } else {
                if ($browse_type == "laps") {
                    $context['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=laptimes' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : ''),
                        $_REQUEST['start'], $context['total'], $context['display']);
                    $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_laptimes'] . ' &gt; ' . $txt['smfg_viewing_laps'] . ' ' . $context['start'] . ' ' . $txt['smfg_to'] . ' ' . $context['end'];
                    $context['linktree'][] = array(
                        'url' => $scripturl . '?action=garage;sa=laptimes' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : '') . (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0'),
                        'name' => $txt['smfg_viewing_laps'] . ' ' . ($context['total'] == 0 ? $context['total'] : $context['start']) . ' ' . $txt['smfg_to'] . ' ' . $context['end'] . '',
                        'extra_after' => ' (' . $txt['smfg_of'] . ' ' . $context['total'] . ' ' . $txt['smfg_total_laps'] . ')'
                    );
                    $context['browse_type_total'] = $txt['smfg_laps_lower'];
                } else {
                    if ($browse_type == "modifications") {
                        $context['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=modifications' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : ''),
                            $_REQUEST['start'], $context['total'], $context['display']);
                        $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_modifications'] . ' &gt; ' . $txt['smfg_viewing_modifications'] . ' ' . $context['start'] . ' ' . $txt['smfg_to'] . ' ' . $context['end'];
                        $context['linktree'][] = array(
                            'url' => $scripturl . '?action=garage;sa=modifications' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : '') . (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0'),
                            'name' => $txt['smfg_viewing_modifications'] . ' ' . ($context['total'] == 0 ? $context['total'] : $context['start']) . ' ' . $txt['smfg_to'] . ' ' . $context['end'] . '',
                            'extra_after' => ' (' . $txt['smfg_of'] . ' ' . $context['total'] . ' ' . $txt['smfg_total_mods'] . ')'
                        );
                        $context['browse_type_total'] = $txt['smfg_modifications_lower'];
                    } else {
                        if ($browse_type == "mostmodified") {
                            $context['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=mostmodified' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : ''),
                                $_REQUEST['start'], $context['total'], $context['display']);
                            $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_most_modified'] . ' &gt; ' . $txt['smfg_viewing_vehicles'] . ' ' . $context['start'] . ' ' . $txt['smfg_to'] . ' ' . $context['end'];
                            $context['linktree'][] = array(
                                'url' => $scripturl . '?action=garage;sa=mostmodified' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : '') . (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0'),
                                'name' => $txt['smfg_viewing_vehicles'] . ' ' . ($context['total'] == 0 ? $context['total'] : $context['start']) . ' ' . $txt['smfg_to'] . ' ' . $context['end'] . '',
                                'extra_after' => ' (' . $txt['smfg_of'] . ' ' . $context['total'] . ' ' . $txt['smfg_total_vehicles'] . ')'
                            );
                            $context['browse_type_total'] = $txt['smfg_vehicles'];
                        } else {
                            if ($browse_type == "mostviewed") {
                                $context['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=mostviewed' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : ''),
                                    $_REQUEST['start'], $context['total'], $context['display']);
                                $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_most_viewed'] . ' &gt; ' . $txt['smfg_viewing_vehicles'] . ' ' . $context['start'] . ' ' . $txt['smfg_to'] . ' ' . $context['end'];
                                $context['linktree'][] = array(
                                    'url' => $scripturl . '?action=garage;sa=mostviewed' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : '') . (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0'),
                                    'name' => $txt['smfg_viewing_vehicles'] . ' ' . ($context['total'] == 0 ? $context['total'] : $context['start']) . ' ' . $txt['smfg_to'] . ' ' . $context['end'] . '',
                                    'extra_after' => ' (' . $txt['smfg_of'] . ' ' . $context['total'] . ' ' . $txt['smfg_total_vehicles'] . ')'
                                );
                                $context['browse_type_total'] = $txt['smfg_vehicles'];
                            } else {
                                if ($browse_type == "latestservice") {
                                    $context['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=latestservice' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : ''),
                                        $_REQUEST['start'], $context['total'], $context['display']);
                                    $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_latest_service'] . ' &gt; ' . $txt['smfg_viewing_vehicles'] . ' ' . $context['start'] . ' ' . $txt['smfg_to'] . ' ' . $context['end'];
                                    $context['linktree'][] = array(
                                        'url' => $scripturl . '?action=garage;sa=latestservice' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : '') . (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0'),
                                        'name' => $txt['smfg_viewing_vehicles'] . ' ' . ($context['total'] == 0 ? $context['total'] : $context['start']) . ' ' . $txt['smfg_to'] . ' ' . $context['end'] . '',
                                        'extra_after' => ' (' . $txt['smfg_of'] . ' ' . $context['total'] . ' ' . $txt['smfg_total_vehicles'] . ')'
                                    );
                                    $context['browse_type_total'] = $txt['smfg_vehicles'];
                                } else {
                                    if ($browse_type == "toprated") {
                                        $context['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=toprated' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : ''),
                                            $_REQUEST['start'], $context['total'], $context['display']);
                                        $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_top_rated'] . ' &gt; ' . $txt['smfg_viewing_vehicles'] . ' ' . $context['start'] . ' ' . $txt['smfg_to'] . ' ' . $context['end'];
                                        $context['linktree'][] = array(
                                            'url' => $scripturl . '?action=garage;sa=toprated' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : '') . (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0'),
                                            'name' => $txt['smfg_viewing_vehicles'] . ' ' . ($context['total'] == 0 ? $context['total'] : $context['start']) . ' ' . $txt['smfg_to'] . ' ' . $context['end'] . '',
                                            'extra_after' => ' (' . $txt['smfg_of'] . ' ' . $context['total'] . ' ' . $txt['smfg_total_vehicles'] . ')'
                                        );
                                        $context['browse_type_total'] = $txt['smfg_vehicles'];
                                    } else {
                                        if ($browse_type == "mostspent") {
                                            $context['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=mostspent' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : ''),
                                                $_REQUEST['start'], $context['total'], $context['display']);
                                            $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_most_spent'] . ' &gt; ' . $txt['smfg_viewing_vehicles'] . ' ' . $context['start'] . ' ' . $txt['smfg_to'] . ' ' . $context['end'];
                                            $context['linktree'][] = array(
                                                'url' => $scripturl . '?action=garage;sa=mostspent' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : '') . (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0'),
                                                'name' => $txt['smfg_viewing_vehicles'] . ' ' . ($context['total'] == 0 ? $context['total'] : $context['start']) . ' ' . $txt['smfg_to'] . ' ' . $context['end'] . '',
                                                'extra_after' => ' (' . $txt['smfg_of'] . ' ' . $context['total'] . ' ' . $txt['smfg_total_vehicles'] . ')'
                                            );
                                            $context['browse_type_total'] = $txt['smfg_vehicles'];
                                        } else {
                                            if ($browse_type == "latestblog") {
                                                $context['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=latestblog' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : ''),
                                                    $_REQUEST['start'], $context['total'], $context['display']);
                                                $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_latest_blog'] . ' &gt; ' . $txt['smfg_viewing_entries'] . ' ' . $context['start'] . ' ' . $txt['smfg_to'] . ' ' . $context['end'];
                                                $context['linktree'][] = array(
                                                    'url' => $scripturl . '?action=garage;sa=latestblog' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : '') . (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0'),
                                                    'name' => $txt['smfg_viewing_entries'] . ' ' . ($context['total'] == 0 ? $context['total'] : $context['start']) . ' ' . $txt['smfg_to'] . ' ' . $context['end'] . '',
                                                    'extra_after' => ' (' . $txt['smfg_of'] . ' ' . $context['total'] . ' ' . $txt['smfg_total_entries'] . ')'
                                                );
                                                $context['browse_type_total'] = $txt['smfg_entries_lower'];
                                            } else {
                                                if ($browse_type == "latestvideo") {
                                                    $context['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=latestvideo' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : ''),
                                                        $_REQUEST['start'], $context['total'], $context['display']);
                                                    $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_latest_video'] . ' &gt; ' . $txt['smfg_viewing_videos'] . ' ' . $context['start'] . ' ' . $txt['smfg_to'] . ' ' . $context['end'];
                                                    $context['linktree'][] = array(
                                                        'url' => $scripturl . '?action=garage;sa=latestvideo' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : '') . (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0'),
                                                        'name' => $txt['smfg_viewing_videos'] . ' ' . ($context['total'] == 0 ? $context['total'] : $context['start']) . ' ' . $txt['smfg_to'] . ' ' . $context['end'] . '',
                                                        'extra_after' => ' (' . $txt['smfg_of'] . ' ' . $context['total'] . ' ' . $txt['smfg_total_videos'] . ')'
                                                    );
                                                    $context['browse_type_total'] = $txt['smfg_videos_lower'];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    $request = $smcFunc['db_query']('', '
        SELECT ' . $browse_selects . '
        FROM ' . $browse_tables . ' ' . $group_by . '
        ORDER BY ' . $sort . ' ' . $order . '
        LIMIT ' . $_REQUEST['start'] . ', ' . $context['display'],
        array(// no values
        )
    );

    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {

        if ($browse_type == "vehicles") {

            list($context['browse_tables'][$count]['vid'],
                $context['browse_tables'][$count]['made_year'],
                $context['browse_tables'][$count]['make'],
                $context['browse_tables'][$count]['model'],
                $context['browse_tables'][$count]['color'],
                $context['browse_tables'][$count]['user_id'],
                $context['browse_tables'][$count]['memberName'],
                $context['browse_tables'][$count]['views'],
                $context['browse_tables'][$count]['total_mods'],
                $context['browse_tables'][$count]['date_updated']) = $row;
            $context['browse_tables'][$count]['views'] = number_format($context['browse_tables'][$count]['views'], 0,
                '.', ',');

            // Check for images
            $request2 = $smcFunc['db_query']('', '
                    SELECT image_id
                    FROM {db_prefix}garage_vehicles_gallery
                    WHERE vehicle_id = {int:vid}
                        AND hilite = 1',
                array(
                    'vid' => $context['browse_tables'][$count]['vid'],
                )
            );
            list($context['browse_tables'][$count]['image_id']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result'] ($request2);
            // If there is an image, lets find its attributes
            $context['browse_tables'][$count]['image'] = '';
            if (isset($context['browse_tables'][$count]['image_id'])) {
                $request2 = $smcFunc['db_query']('', '
                        SELECT attach_location, attach_ext, attach_file, attach_desc, is_remote
                        FROM {db_prefix}garage_images
                        WHERE attach_id = {int:image_id}',
                    array(
                        'image_id' => $context['browse_tables'][$count]['image_id'],
                    )
                );
                list($context['browse_tables'][$count]['attach_location'],
                    $context['browse_tables'][$count]['attach_ext'],
                    $context['browse_tables'][$count]['attach_file'],
                    $context['browse_tables'][$count]['attach_desc'],
                    $context['browse_tables'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
                $smcFunc['db_free_result'] ($request2);
                if (empty($context['browse_tables'][$count]['attach_desc'])) {
                    $context['browse_tables'][$count]['attach_desc'] = '<i>' . $txt['smfg_no_desc'] . '</i>';
                }

                // Build appropriate links for remote images
                if (isset($context['browse_tables'][$count]['attach_location'])) {
                    if ($context['browse_tables'][$count]['is_remote'] == 1) {
                        $context['browse_tables'][$count]['attach_location'] = urldecode($context['browse_tables'][$count]['attach_location']);
                    } else {
                        $context['browse_tables'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['browse_tables'][$count]['attach_location'];
                    }
                }
                // If there is an image attached, link to it
                if (isset($context['browse_tables'][$count]['attach_location'])) {
                    $context['browse_tables'][$count]['image'] = "<a href=\"" . $context['browse_tables'][$count]['attach_location'] . "\" rel=\"shadowbox\" title=\"" . garage_title_clean($context['browse_tables'][$count]['made_year'] . ' ' . $context['browse_tables'][$count]['make'] . ' ' . $context['browse_tables'][$count]['model'] . ' :: ' . $context['browse_tables'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_camera.gif\" width=\"16\" height=\"16\" /></a>";
                }
            }

            // Define spacer
            $context['browse_tables'][$count]['spacer'] = '';

            if ($smfgSettings['enable_vehicle_video']) {

                // Check for videos
                $request2 = $smcFunc['db_query']('', '
                        SELECT video_id
                        FROM {db_prefix}garage_video_gallery
                        WHERE vehicle_id = {int:vid}
                            ORDER BY id ASC
                            LIMIT 1',
                    array(
                        'vid' => $context['browse_tables'][$count]['vid'],
                    )
                );
                list($context['browse_tables'][$count]['video_id']) = $smcFunc['db_fetch_row']($request2);
                $smcFunc['db_free_result'] ($request2);

                // If there is an video, lets find its attributes
                $context['browse_tables'][$count]['video'] = "";
                if (isset($context['browse_tables'][$count]['video_id'])) {
                    $request2 = $smcFunc['db_query']('', '
                            SELECT url, title, video_desc
                            FROM {db_prefix}garage_video
                            WHERE id = {int:video_id}',
                        array(
                            'video_id' => $context['browse_tables'][$count]['video_id'],
                        )
                    );
                    list($context['browse_tables'][$count]['video_url'],
                        $context['browse_tables'][$count]['video_title'],
                        $context['browse_tables'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
                    $smcFunc['db_free_result'] ($request2);
                    if (empty($context['browse_tables'][$count]['video_desc'])) {
                        $context['browse_tables'][$count]['video_desc'] = '<i>' . $txt['smfg_no_desc'] . '</i>';
                    }
                    $context['browse_tables'][$count]['video_height'] = displayVideo($context['browse_tables'][$count]['video_url'],
                        'height');
                    $context['browse_tables'][$count]['video_width'] = displayVideo($context['browse_tables'][$count]['video_url'],
                        'width');
                    if (!empty($context['browse_tables'][$count]['image_id']) && !empty($context['browse_tables'][$count]['video_id'])) {
                        $context['browse_tables'][$count]['spacer'] = '&nbsp;';
                    }

                    // If there is an video attached, link to it
                    if (isset($context['browse_tables'][$count]['video_url'])) {
                        $context['browse_tables'][$count]['video'] = "<a href=\"" . $scripturl . "?action=garage;sa=video;id=" . $context['browse_tables'][$count]['video_id'] . "\" rel=\"shadowbox;width=" . $context['browse_tables'][$count]['video_width'] . ";height=" . $context['browse_tables'][$count]['video_height'] . "\" title=\"" . garage_title_clean('<b>' . $context['browse_tables'][$count]['video_title'] . '</b> :: ' . $context['browse_tables'][$count]['video_desc']) . "\" class=\"smfg_videoTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_video.gif\" width=\"16\" height=\"16\" /></a>";
                    }
                }

            }

        } else {
            if ($browse_type == "modifications") {

                list($context['browse_tables'][$count]['vid'],
                    $context['browse_tables'][$count]['made_year'],
                    $context['browse_tables'][$count]['make'],
                    $context['browse_tables'][$count]['model'],
                    $context['browse_tables'][$count]['user_id'],
                    $context['browse_tables'][$count]['memberName'],
                    $context['browse_tables'][$count]['mid'],
                    $context['browse_tables'][$count]['modification'],
                    $context['browse_tables'][$count]['manufacturer'],
                    $context['browse_tables'][$count]['category'],
                    $context['browse_tables'][$count]['date_updated']) = $row;

                // Check for images
                $request2 = $smcFunc['db_query']('', '
                    SELECT image_id
                    FROM {db_prefix}garage_modifications_gallery
                    WHERE modification_id = {int:mid}',
                    array(
                        'mid' => $context['browse_tables'][$count]['mid'],
                    )
                );
                list($context['browse_tables'][$count]['image_id']) = $smcFunc['db_fetch_row']($request2);
                $smcFunc['db_free_result'] ($request2);
                // If there is an image, lets find its attributes
                $context['browse_tables'][$count]['image'] = '';
                if (isset($context['browse_tables'][$count]['image_id'])) {
                    $request2 = $smcFunc['db_query']('', '
                        SELECT attach_location, attach_ext, attach_file, attach_desc, is_remote
                        FROM {db_prefix}garage_images
                        WHERE attach_id = {int:image_id}',
                        array(
                            'image_id' => $context['browse_tables'][$count]['image_id'],
                        )
                    );
                    list($context['browse_tables'][$count]['attach_location'],
                        $context['browse_tables'][$count]['attach_ext'],
                        $context['browse_tables'][$count]['attach_file'],
                        $context['browse_tables'][$count]['attach_desc'],
                        $context['browse_tables'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
                    $smcFunc['db_free_result'] ($request2);
                    if (empty($context['browse_tables'][$count]['attach_desc'])) {
                        $context['browse_tables'][$count]['attach_desc'] = '<i>' . $txt['smfg_no_desc'] . '</i>';
                    }

                    // Build appropriate links for remote images
                    if (isset($context['browse_tables'][$count]['attach_location'])) {
                        if ($context['browse_tables'][$count]['is_remote'] == 1) {
                            $context['browse_tables'][$count]['attach_location'] = urldecode($context['browse_tables'][$count]['attach_location']);
                        } else {
                            $context['browse_tables'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['browse_tables'][$count]['attach_location'];
                        }
                    }
                    // If there is an image attached, link to it
                    if (isset($context['browse_tables'][$count]['attach_location'])) {
                        $context['browse_tables'][$count]['image'] = "<a href=\"" . $context['browse_tables'][$count]['attach_location'] . "\" rel=\"shadowbox\" title=\"" . garage_title_clean($context['browse_tables'][$count]['modification'] . ' :: ' . $context['browse_tables'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_camera.gif\" width=\"16\" height=\"16\" /></a>";
                    }
                }

                // Define spacer
                $context['browse_tables'][$count]['spacer'] = '';

                if ($smfgSettings['enable_modification_video']) {

                    // Check for videos
                    $request2 = $smcFunc['db_query']('', '
                        SELECT video_id
                        FROM {db_prefix}garage_video_gallery
                        WHERE vehicle_id = {int:vid}
                            AND type = "mod"
                            AND type_id = {int:mid}
                            ORDER BY id ASC
                            LIMIT 1',
                        array(
                            'vid' => $context['browse_tables'][$count]['vid'],
                            'mid' => $context['browse_tables'][$count]['mid'],
                        )
                    );
                    list($context['browse_tables'][$count]['video_id']) = $smcFunc['db_fetch_row']($request2);
                    $smcFunc['db_free_result'] ($request2);

                    // If there is an video, lets find its attributes
                    $context['browse_tables'][$count]['video'] = "";
                    if (isset($context['browse_tables'][$count]['video_id'])) {
                        $request2 = $smcFunc['db_query']('', '
                            SELECT url, title, video_desc
                            FROM {db_prefix}garage_video
                            WHERE id = {int:video_id}',
                            array(
                                'video_id' => $context['browse_tables'][$count]['video_id'],
                            )
                        );
                        list($context['browse_tables'][$count]['video_url'],
                            $context['browse_tables'][$count]['video_title'],
                            $context['browse_tables'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
                        $smcFunc['db_free_result'] ($request2);
                        if (empty($context['browse_tables'][$count]['video_desc'])) {
                            $context['browse_tables'][$count]['video_desc'] = '<i>' . $txt['smfg_no_desc'] . '</i>';
                        }
                        $context['browse_tables'][$count]['video_height'] = displayVideo($context['browse_tables'][$count]['video_url'],
                            'height');
                        $context['browse_tables'][$count]['video_width'] = displayVideo($context['browse_tables'][$count]['video_url'],
                            'width');
                        if (!empty($context['browse_tables'][$count]['image_id']) && !empty($context['browse_tables'][$count]['video_id'])) {
                            $context['browse_tables'][$count]['spacer'] = '&nbsp;';
                        }

                        // If there is an video attached, link to it
                        if (isset($context['browse_tables'][$count]['video_url'])) {
                            $context['browse_tables'][$count]['video'] = "<a href=\"" . $scripturl . "?action=garage;sa=video;id=" . $context['browse_tables'][$count]['video_id'] . "\" rel=\"shadowbox;width=" . $context['browse_tables'][$count]['video_width'] . ";height=" . $context['browse_tables'][$count]['video_height'] . "\" title=\"" . garage_title_clean('<b>' . $context['browse_tables'][$count]['video_title'] . '</b> :: ' . $context['browse_tables'][$count]['video_desc']) . "\" class=\"smfg_videoTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_video.gif\" width=\"16\" height=\"16\" /></a>";
                        }
                    }

                    // Mods tooltip
                    $context['browse_tables'][$count]['mod_tooltip'] = garage_title_clean($context['browse_tables'][$count]['modification']) . " :: ";
                    if (!empty($context['browse_tables'][$count]['manufacturer'])) {
                        $context['browse_tables'][$count]['mod_tooltip'] .= "<b>" . $txt['smfg_manufacturer'] . ":</b> " . $context['browse_tables'][$count]['manufacturer'];
                    }
                    if (!empty($context['browse_tables'][$count]['category'])) {
                        $context['browse_tables'][$count]['mod_tooltip'] .= "<br /><b>" . $txt['smfg_category'] . ":</b> " . $context['browse_tables'][$count]['category'];
                    }


                }

            } else {
                if ($browse_type == "quartermiles") {

                    list($context['browse_tables'][$count]['qmid'],
                        $context['browse_tables'][$count]['vid'],
                        $context['browse_tables'][$count]['vehicle'],
                        $context['browse_tables'][$count]['user_id'],
                        $context['browse_tables'][$count]['memberName'],
                        $context['browse_tables'][$count]['rt'],
                        $context['browse_tables'][$count]['sixty'],
                        $context['browse_tables'][$count]['three'],
                        $context['browse_tables'][$count]['eighth'],
                        $context['browse_tables'][$count]['eighthmph'],
                        $context['browse_tables'][$count]['thou'],
                        $context['browse_tables'][$count]['quart'],
                        $context['browse_tables'][$count]['quartmph']) = $row;

                    // Check for images
                    $request2 = $smcFunc['db_query']('', '
                    SELECT image_id
                    FROM {db_prefix}garage_quartermiles_gallery
                    WHERE quartermile_id = {int:qmid}',
                        array(
                            'qmid' => $context['browse_tables'][$count]['qmid'],
                        )
                    );
                    list($context['browse_tables'][$count]['image_id']) = $smcFunc['db_fetch_row']($request2);
                    $smcFunc['db_free_result'] ($request2);
                    // If there is an image, lets find its attributes
                    $context['browse_tables'][$count]['image'] = '';
                    if (isset($context['browse_tables'][$count]['image_id'])) {
                        $request2 = $smcFunc['db_query']('', '
                        SELECT attach_location, attach_ext, attach_file, attach_desc, is_remote
                        FROM {db_prefix}garage_images
                        WHERE attach_id = {int:image_id}',
                            array(
                                'image_id' => $context['browse_tables'][$count]['image_id'],
                            )
                        );
                        list($context['browse_tables'][$count]['attach_location'],
                            $context['browse_tables'][$count]['attach_ext'],
                            $context['browse_tables'][$count]['attach_file'],
                            $context['browse_tables'][$count]['attach_desc'],
                            $context['browse_tables'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
                        $smcFunc['db_free_result'] ($request2);
                        if (empty($context['browse_tables'][$count]['attach_desc'])) {
                            $context['browse_tables'][$count]['attach_desc'] = '<i>' . $txt['smfg_no_desc'] . '</i>';
                        }

                        // Build appropriate links for remote images
                        if (isset($context['browse_tables'][$count]['attach_location'])) {
                            if ($context['browse_tables'][$count]['is_remote'] == 1) {
                                $context['browse_tables'][$count]['attach_location'] = urldecode($context['browse_tables'][$count]['attach_location']);
                            } else {
                                $context['browse_tables'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['browse_tables'][$count]['attach_location'];
                            }
                        }
                        // If there is an image attached, link to it
                        if (isset($context['browse_tables'][$count]['attach_location'])) {
                            $context['browse_tables'][$count]['image'] = "<a href=\"" . $context['browse_tables'][$count]['attach_location'] . "\" rel=\"shadowbox\" title=\"" . garage_title_clean($context['browse_tables'][$count]['quart'] . ' @ ' . $context['browse_tables'][$count]['quartmph'] . ' :: ' . $context['browse_tables'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_camera.gif\" width=\"16\" height=\"16\" /></a>";
                        }
                    }

                    // Define spacer
                    $context['browse_tables'][$count]['spacer'] = '';

                    if ($smfgSettings['enable_quartermile_video']) {

                        // Check for videos
                        $request2 = $smcFunc['db_query']('', '
                        SELECT video_id
                        FROM {db_prefix}garage_video_gallery
                        WHERE vehicle_id = {int:vid}
                            AND type = "qmile"
                            AND type_id = {int:qmid}
                            ORDER BY id ASC
                            LIMIT 1',
                            array(
                                'vid' => $context['browse_tables'][$count]['vid'],
                                'qmid' => $context['browse_tables'][$count]['qmid'],
                            )
                        );
                        list($context['browse_tables'][$count]['video_id']) = $smcFunc['db_fetch_row']($request2);
                        $smcFunc['db_free_result'] ($request2);

                        // If there is an video, lets find its attributes
                        $context['browse_tables'][$count]['video'] = "";
                        if (isset($context['browse_tables'][$count]['video_id'])) {
                            $request2 = $smcFunc['db_query']('', '
                            SELECT url, title, video_desc
                            FROM {db_prefix}garage_video
                            WHERE id = {int:video_id}',
                                array(
                                    'video_id' => $context['browse_tables'][$count]['video_id'],
                                )
                            );
                            list($context['browse_tables'][$count]['video_url'],
                                $context['browse_tables'][$count]['video_title'],
                                $context['browse_tables'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
                            $smcFunc['db_free_result'] ($request2);
                            if (empty($context['browse_tables'][$count]['video_desc'])) {
                                $context['browse_tables'][$count]['video_desc'] = '<i>' . $txt['smfg_no_desc'] . '</i>';
                            }
                            $context['browse_tables'][$count]['video_height'] = displayVideo($context['browse_tables'][$count]['video_url'],
                                'height');
                            $context['browse_tables'][$count]['video_width'] = displayVideo($context['browse_tables'][$count]['video_url'],
                                'width');
                            if (!empty($context['browse_tables'][$count]['image_id']) && !empty($context['browse_tables'][$count]['video_id'])) {
                                $context['browse_tables'][$count]['spacer'] = '&nbsp;';
                            }

                            // If there is an video attached, link to it
                            if (isset($context['browse_tables'][$count]['video_url'])) {
                                $context['browse_tables'][$count]['video'] = "<a href=\"" . $scripturl . "?action=garage;sa=video;id=" . $context['browse_tables'][$count]['video_id'] . "\" rel=\"shadowbox;width=" . $context['browse_tables'][$count]['video_width'] . ";height=" . $context['browse_tables'][$count]['video_height'] . "\" title=\"" . garage_title_clean('<b>' . $context['browse_tables'][$count]['video_title'] . '</b> :: ' . $context['browse_tables'][$count]['video_desc']) . "\" class=\"smfg_videoTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_video.gif\" width=\"16\" height=\"16\" /></a>";
                            }
                        }

                    }

                } else {
                    if ($browse_type == "dynoruns") {

                        list($context['browse_tables'][$count]['vid'],
                            $context['browse_tables'][$count]['did'],
                            $context['browse_tables'][$count]['vehicle'],
                            $context['browse_tables'][$count]['user_id'],
                            $context['browse_tables'][$count]['memberName'],
                            $context['browse_tables'][$count]['bhp'],
                            $context['browse_tables'][$count]['bhp_unit'],
                            $context['browse_tables'][$count]['torque'],
                            $context['browse_tables'][$count]['torque_unit'],
                            $context['browse_tables'][$count]['boost'],
                            $context['browse_tables'][$count]['boost_unit'],
                            $context['browse_tables'][$count]['nitrous'],
                            $context['browse_tables'][$count]['peakpoint'],
                            $context['browse_tables'][$count]['dynocenter'],
                            $context['browse_tables'][$count]['dynocenter_id']) = $row;

                        // Check for images
                        $request2 = $smcFunc['db_query']('', '
                    SELECT image_id
                    FROM {db_prefix}garage_dynoruns_gallery
                    WHERE dynorun_id = {int:did}',
                            array(
                                'did' => $context['browse_tables'][$count]['did'],
                            )
                        );
                        list($context['browse_tables'][$count]['image_id']) = $smcFunc['db_fetch_row']($request2);
                        $smcFunc['db_free_result'] ($request2);
                        // If there is an image, lets find its attributes
                        $context['browse_tables'][$count]['image'] = '';
                        if (isset($context['browse_tables'][$count]['image_id'])) {
                            $request2 = $smcFunc['db_query']('', '
                        SELECT attach_location, attach_ext, attach_file, attach_desc, is_remote
                        FROM {db_prefix}garage_images
                        WHERE attach_id = {int:image_id}',
                                array(
                                    'image_id' => $context['browse_tables'][$count]['image_id'],
                                )
                            );
                            list($context['browse_tables'][$count]['attach_location'],
                                $context['browse_tables'][$count]['attach_ext'],
                                $context['browse_tables'][$count]['attach_file'],
                                $context['browse_tables'][$count]['attach_desc'],
                                $context['browse_tables'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
                            $smcFunc['db_free_result'] ($request2);
                            if (empty($context['browse_tables'][$count]['attach_desc'])) {
                                $context['browse_tables'][$count]['attach_desc'] = '<i>' . $txt['smfg_no_desc'] . '</i>';
                            }

                            // Build appropriate links for remote images
                            if (isset($context['browse_tables'][$count]['attach_location'])) {
                                if ($context['browse_tables'][$count]['is_remote'] == 1) {
                                    $context['browse_tables'][$count]['attach_location'] = urldecode($context['browse_tables'][$count]['attach_location']);
                                } else {
                                    $context['browse_tables'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['browse_tables'][$count]['attach_location'];
                                }
                            }
                            // If there is an image attached, link to it
                            if (isset($context['browse_tables'][$count]['attach_location'])) {
                                $context['browse_tables'][$count]['image'] = "<a href=\"" . $context['browse_tables'][$count]['attach_location'] . "\" rel=\"shadowbox\" title=\"" . garage_title_clean($context['browse_tables'][$count]['bhp'] . ' ' . $context['browse_tables'][$count]['bhp_unit'] . ' :: ' . $context['browse_tables'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_camera.gif\" width=\"16\" height=\"16\" /></a>";
                            }
                        }

                        // Define spacer
                        $context['browse_tables'][$count]['spacer'] = '';

                        if ($smfgSettings['enable_dynorun_video']) {

                            // Check for videos
                            $request2 = $smcFunc['db_query']('', '
                        SELECT video_id
                        FROM {db_prefix}garage_video_gallery
                        WHERE vehicle_id = {int:vid}
                            AND type = "dynorun"
                            AND type_id = {int:did}
                            ORDER BY id ASC
                            LIMIT 1',
                                array(
                                    'vid' => $context['browse_tables'][$count]['vid'],
                                    'did' => $context['browse_tables'][$count]['did'],
                                )
                            );
                            list($context['browse_tables'][$count]['video_id']) = $smcFunc['db_fetch_row']($request2);
                            $smcFunc['db_free_result'] ($request2);

                            // If there is an video, lets find its attributes
                            $context['browse_tables'][$count]['video'] = "";
                            if (isset($context['browse_tables'][$count]['video_id'])) {
                                $request2 = $smcFunc['db_query']('', '
                            SELECT url, title, video_desc
                            FROM {db_prefix}garage_video
                            WHERE id = {int:video_id}',
                                    array(
                                        'video_id' => $context['browse_tables'][$count]['video_id'],
                                    )
                                );
                                list($context['browse_tables'][$count]['video_url'],
                                    $context['browse_tables'][$count]['video_title'],
                                    $context['browse_tables'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
                                $smcFunc['db_free_result'] ($request2);
                                if (empty($context['browse_tables'][$count]['video_desc'])) {
                                    $context['browse_tables'][$count]['video_desc'] = '<i>' . $txt['smfg_no_desc'] . '</i>';
                                }
                                $context['browse_tables'][$count]['video_height'] = displayVideo($context['browse_tables'][$count]['video_url'],
                                    'height');
                                $context['browse_tables'][$count]['video_width'] = displayVideo($context['browse_tables'][$count]['video_url'],
                                    'width');
                                if (!empty($context['browse_tables'][$count]['image_id']) && !empty($context['browse_tables'][$count]['video_id'])) {
                                    $context['browse_tables'][$count]['spacer'] = '&nbsp;';
                                }

                                // If there is an video attached, link to it
                                if (isset($context['browse_tables'][$count]['video_url'])) {
                                    $context['browse_tables'][$count]['video'] = "<a href=\"" . $scripturl . "?action=garage;sa=video;id=" . $context['browse_tables'][$count]['video_id'] . "\" rel=\"shadowbox;width=" . $context['browse_tables'][$count]['video_width'] . ";height=" . $context['browse_tables'][$count]['video_height'] . "\" title=\"" . garage_title_clean('<b>' . $context['browse_tables'][$count]['video_title'] . '</b> :: ' . $context['browse_tables'][$count]['video_desc']) . "\" class=\"smfg_videoTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_video.gif\" width=\"16\" height=\"16\" /></a>";
                                }
                            }

                        }

                    } else {
                        if ($browse_type == "laps") {

                            list($context['browse_tables'][$count]['vid'],
                                $context['browse_tables'][$count]['vehicle'],
                                $context['browse_tables'][$count]['user_id'],
                                $context['browse_tables'][$count]['memberName'],
                                $context['browse_tables'][$count]['tid'],
                                $context['browse_tables'][$count]['track'],
                                $context['browse_tables'][$count]['condition'],
                                $context['browse_tables'][$count]['type'],
                                $context['browse_tables'][$count]['lid'],
                                $context['browse_tables'][$count]['time']) = $row;

                            // Check for images
                            $request2 = $smcFunc['db_query']('', '
                    SELECT image_id
                    FROM {db_prefix}garage_laps_gallery
                    WHERE lap_id = {int:lid}',
                                array(
                                    'lid' => $context['browse_tables'][$count]['lid'],
                                )
                            );
                            list($context['browse_tables'][$count]['image_id']) = $smcFunc['db_fetch_row']($request2);
                            $smcFunc['db_free_result'] ($request2);
                            // If there is an image, lets find its attributes
                            $context['browse_tables'][$count]['image'] = '';
                            if (isset($context['browse_tables'][$count]['image_id'])) {
                                $request2 = $smcFunc['db_query']('', '
                        SELECT attach_location, attach_ext, attach_file, attach_desc, is_remote
                        FROM {db_prefix}garage_images
                        WHERE attach_id = {int:image_id}',
                                    array(
                                        'image_id' => $context['browse_tables'][$count]['image_id'],
                                    )
                                );
                                list($context['browse_tables'][$count]['attach_location'],
                                    $context['browse_tables'][$count]['attach_ext'],
                                    $context['browse_tables'][$count]['attach_file'],
                                    $context['browse_tables'][$count]['attach_desc'],
                                    $context['browse_tables'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
                                $smcFunc['db_free_result'] ($request2);
                                if (empty($context['browse_tables'][$count]['attach_desc'])) {
                                    $context['browse_tables'][$count]['attach_desc'] = '<i>' . $txt['smfg_no_desc'] . '</i>';
                                }

                                // Build appropriate links for remote images
                                if (isset($context['browse_tables'][$count]['attach_location'])) {
                                    if ($context['browse_tables'][$count]['is_remote'] == 1) {
                                        $context['browse_tables'][$count]['attach_location'] = urldecode($context['browse_tables'][$count]['attach_location']);
                                    } else {
                                        $context['browse_tables'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['browse_tables'][$count]['attach_location'];
                                    }
                                }
                                // If there is an image attached, link to it
                                if (isset($context['browse_tables'][$count]['attach_location'])) {
                                    $context['browse_tables'][$count]['image'] = "<a href=\"" . $context['browse_tables'][$count]['attach_location'] . "\" rel=\"shadowbox\" title=\"" . garage_title_clean($context['browse_tables'][$count]['time'] . ' @ ' . $context['browse_tables'][$count]['track'] . ' :: ' . $context['browse_tables'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_camera.gif\" width=\"16\" height=\"16\" /></a>";
                                }
                            }

                            // Define spacer
                            $context['browse_tables'][$count]['spacer'] = '';

                            if ($smfgSettings['enable_laptime_video']) {

                                // Check for videos
                                $request2 = $smcFunc['db_query']('', '
                        SELECT video_id
                        FROM {db_prefix}garage_video_gallery
                        WHERE vehicle_id = {int:vid}
                            AND type = "lap"
                            AND type_id = {int:lid}
                            ORDER BY id ASC
                            LIMIT 1',
                                    array(
                                        'vid' => $context['browse_tables'][$count]['vid'],
                                        'lid' => $context['browse_tables'][$count]['lid'],
                                    )
                                );
                                list($context['browse_tables'][$count]['video_id']) = $smcFunc['db_fetch_row']($request2);
                                $smcFunc['db_free_result'] ($request2);

                                // If there is an video, lets find its attributes
                                $context['browse_tables'][$count]['video'] = "";
                                if (isset($context['browse_tables'][$count]['video_id'])) {
                                    $request2 = $smcFunc['db_query']('', '
                            SELECT url, title, video_desc
                            FROM {db_prefix}garage_video
                            WHERE id = {int:video_id}',
                                        array(
                                            'video_id' => $context['browse_tables'][$count]['video_id'],
                                        )
                                    );
                                    list($context['browse_tables'][$count]['video_url'],
                                        $context['browse_tables'][$count]['video_title'],
                                        $context['browse_tables'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
                                    $smcFunc['db_free_result'] ($request2);
                                    if (empty($context['browse_tables'][$count]['video_desc'])) {
                                        $context['browse_tables'][$count]['video_desc'] = '<i>' . $txt['smfg_no_desc'] . '</i>';
                                    }
                                    $context['browse_tables'][$count]['video_height'] = displayVideo($context['browse_tables'][$count]['video_url'],
                                        'height');
                                    $context['browse_tables'][$count]['video_width'] = displayVideo($context['browse_tables'][$count]['video_url'],
                                        'width');
                                    if (!empty($context['browse_tables'][$count]['image_id']) && !empty($context['browse_tables'][$count]['video_id'])) {
                                        $context['browse_tables'][$count]['spacer'] = '&nbsp;';
                                    }

                                    // If there is an video attached, link to it
                                    if (isset($context['browse_tables'][$count]['video_url'])) {
                                        $context['browse_tables'][$count]['video'] = "<a href=\"" . $scripturl . "?action=garage;sa=video;id=" . $context['browse_tables'][$count]['video_id'] . "\" rel=\"shadowbox;width=" . $context['browse_tables'][$count]['video_width'] . ";height=" . $context['browse_tables'][$count]['video_height'] . "\" title=\"" . garage_title_clean('<b>' . $context['browse_tables'][$count]['video_title'] . '</b> :: ' . $context['browse_tables'][$count]['video_desc']) . "\" class=\"smfg_videoTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_video.gif\" width=\"16\" height=\"16\" /></a>";
                                    }
                                }

                            }

                        } else {
                            if ($browse_type == "mostmodified") {

                                list($context['browse_tables'][$count]['vid'],
                                    $context['browse_tables'][$count]['total_mods'],
                                    $context['browse_tables'][$count]['user_id'],
                                    $context['browse_tables'][$count]['made_year'],
                                    $context['browse_tables'][$count]['make'],
                                    $context['browse_tables'][$count]['model'],
                                    $context['browse_tables'][$count]['memberName']) = $row;

                            } else {
                                if ($browse_type == "mostviewed") {

                                    list($context['browse_tables'][$count]['vid'],
                                        $context['browse_tables'][$count]['made_year'],
                                        $context['browse_tables'][$count]['make'],
                                        $context['browse_tables'][$count]['model'],
                                        $context['browse_tables'][$count]['views'],
                                        $context['browse_tables'][$count]['user_id'],
                                        $context['browse_tables'][$count]['memberName']) = $row;

                                } else {
                                    if ($browse_type == "latestservice") {

                                        list($context['browse_tables'][$count]['vid'],
                                            $context['browse_tables'][$count]['made_year'],
                                            $context['browse_tables'][$count]['make'],
                                            $context['browse_tables'][$count]['model'],
                                            $context['browse_tables'][$count]['user_id'],
                                            $context['browse_tables'][$count]['memberName'],
                                            $context['browse_tables'][$count]['type'],
                                            $context['browse_tables'][$count]['created']) = $row;

                                    } else {
                                        if ($browse_type == "toprated") {

                                            list($context['browse_tables'][$count]['vid'],
                                                $context['browse_tables'][$count]['rating'],
                                                $context['browse_tables'][$count]['poss_rating'],
                                                $context['browse_tables'][$count]['made_year'],
                                                $context['browse_tables'][$count]['make'],
                                                $context['browse_tables'][$count]['model'],
                                                $context['browse_tables'][$count]['user_id'],
                                                $context['browse_tables'][$count]['memberName']) = $row;
                                            if ($context['browse_tables'][$count]['rating'] > 0) {
                                                $context['browse_tables'][$count]['rating'] = number_format($context['browse_tables'][$count]['rating'],
                                                    2, '.', ',');
                                            } else {
                                                $context['browse_tables'][$count]['rating'] = 0;
                                            }

                                        } else {
                                            if ($browse_type == "mostspent") {

                                                list($context['browse_tables'][$count]['vid'],
                                                    $context['browse_tables'][$count]['total_spent'],
                                                    $context['browse_tables'][$count]['made_year'],
                                                    $context['browse_tables'][$count]['make'],
                                                    $context['browse_tables'][$count]['model'],
                                                    $context['browse_tables'][$count]['user_id'],
                                                    $context['browse_tables'][$count]['memberName'],
                                                    $context['browse_tables'][$count]['currency']) = $row;
                                                $context['browse_tables'][$count]['total_spent'] = number_format($context['browse_tables'][$count]['total_spent'],
                                                    2, '.', ',');

                                            } else {
                                                if ($browse_type == "latestblog") {

                                                    list($context['browse_tables'][$count]['vid'],
                                                        $context['browse_tables'][$count]['blog_title'],
                                                        $context['browse_tables'][$count]['made_year'],
                                                        $context['browse_tables'][$count]['make'],
                                                        $context['browse_tables'][$count]['model'],
                                                        $context['browse_tables'][$count]['user_id'],
                                                        $context['browse_tables'][$count]['memberName'],
                                                        $context['browse_tables'][$count]['posted_date']) = $row;
                                                    $context['browse_tables'][$count]['blog_title'] = smfg_trim($context['browse_tables'][$count]['blog_title']);

                                                } else {
                                                    if ($browse_type == "latestvideo") {

                                                        list($context['browse_tables'][$count]['id'],
                                                            $context['browse_tables'][$count]['vid'],
                                                            $context['browse_tables'][$count]['video_title'],
                                                            $context['browse_tables'][$count]['made_year'],
                                                            $context['browse_tables'][$count]['make'],
                                                            $context['browse_tables'][$count]['model'],
                                                            $context['browse_tables'][$count]['user_id'],
                                                            $context['browse_tables'][$count]['memberName'],
                                                            $context['browse_tables'][$count]['video_type'],
                                                            $context['browse_tables'][$count]['tid'],
                                                            $context['browse_tables'][$count]['video_url'],
                                                            $context['browse_tables'][$count]['video_desc']) = $row;
                                                        $context['browse_tables'][$count]['video_title'] = smfg_trim($context['browse_tables'][$count]['video_title']);
                                                        if (empty($context['browse_tables'][$count]['video_desc'])) {
                                                            $context['browse_tables'][$count]['video_desc'] = '<i>' . $txt['smfg_no_desc'] . '</i>';
                                                        }
                                                        $context['browse_tables'][$count]['video_height'] = displayVideo($context['browse_tables'][$count]['video_url'],
                                                            'height');
                                                        $context['browse_tables'][$count]['video_width'] = displayVideo($context['browse_tables'][$count]['video_url'],
                                                            'width');
                                                        if (isset($context['browse_tables'][$count]['video_url'])) {
                                                            $context['browse_tables'][$count]['video'] = "<a href=\"" . $scripturl . "?action=garage;sa=video;id=" . $context['browse_tables'][$count]['id'] . "\" rel=\"shadowbox;width=" . $context['browse_tables'][$count]['video_width'] . ";height=" . $context['browse_tables'][$count]['video_height'] . "\" title=\"" . garage_title_clean('<b>' . $context['browse_tables'][$count]['video_title'] . '</b> :: ' . $context['browse_tables'][$count]['video_desc']) . "\" class=\"smfg_videoTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_video.gif\" width=\"16\" height=\"16\" /></a>";
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $count++;
    }
    $smcFunc['db_free_result'] ($request);

    // Re-enable query check
    if ($browse_type == "mostspent") {

        // *************************************************************
        // WARNING: The query check is being enabled, this MUST BE DONE!
        // *************************************************************
        $modSettings['disableQueryCheck'] = 0;
        // *************************************************************

    }

}

// Builds SMF 2 Sub Menu for Garage
function build_submenu($links)
{
    global $context, $txt, $smcFunc, $scripturl;

    // Select User's Vehicle(s) for the menu
    $request = $smcFunc['db_query']('', '
        SELECT v.id, CONCAT_WS(" ", v.made_year, mk.make, md.model)
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
        WHERE v.user_id = {int:user_id}
            AND v.make_id = mk.id
            AND v.model_id = md.id',
        array(
            'user_id' => $context['user']['id'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list ($context['menu_vehicles'][$count]['veh_id'],
            $context['menu_vehicles'][$count]['vehicle']) = $row;
        $count++;
    }
    $smcFunc['db_free_result']($request);

    echo '
    <ul class="dropmenu">';

    foreach ($links as $link) {
        echo '
        <li id="button_' . $link['action'] . '">
            <a class="' . ($link['selected'] ? 'active ' : '') . 'firstlevel" href="' . $scripturl . '?action=garage' . (!empty($link['action']) ? ';sa=' . $link['action'] : '') . '">
                <span class="firstlevel">', $link['label'], '</span>
            </a>';
        if ($link['action'] == 'user_garage') {
            echo '
                <ul>';

            echo '
                <li>
                    <a href="' . $scripturl . '?action=garage;sa=add_vehicle">
                        <span>' . $txt['smfg_create_vehicle'] . '</span>
                    </a>
                </li>';

            if (!empty($context['menu_vehicles'])) {
                foreach ($context['menu_vehicles'] as $vehicle) {
                    echo '
                        <li>
                            <a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $vehicle['veh_id'] . '">
                                <span>' . $vehicle['vehicle'] . '</span>
                            </a>
                        </li>';
                }
            }

            echo '
                </ul>
                ';
        }
        echo '
        </li>';
    }

    echo '
    </ul><br /><br />';

}

/**
 * Parses a video URL and returns embed HTML, thumbnail, dimensions, or validates the URL.
 *
 * @param string $url The video URL to parse
 * @param mixed $type What to return:
 *   1 = embed HTML, 2 = thumbnail URL, 3 = video ID, 4 = TRUE if valid,
 *   'height' = height, 'width' = width
 * @return mixed Depends on $type
 */
function displayVideo($url, $type)
{
    global $settings;

    $embed = array();

    // YouTube - standard watch URLs
    $embed[] = array(
        'name' => 'YouTube',
        'enabled' => 1,
        'pattern' => 'https?://(?:(?:www|m)\.)?youtube\.com/watch\?(?:.*?)v=([\w-]+)(?:.*?)(?:[&#?]t=(\d+))?',
        'embedlink' => 'https://www.youtube.com/embed/$1?start=$2',
        'width' => '640',
        'height' => '360',
        'thumb' => 'https://img.youtube.com/vi/$1/hqdefault.jpg',
        'video_id' => '$1',
    );

    // YouTube - short URLs (youtu.be)
    $embed[] = array(
        'name' => 'YouTube Short URL',
        'enabled' => 1,
        'pattern' => 'https?://youtu\.be/([\w-]+)(?:\?.*?)?',
        'embedlink' => 'https://www.youtube.com/embed/$1',
        'width' => '640',
        'height' => '360',
        'thumb' => 'https://img.youtube.com/vi/$1/hqdefault.jpg',
        'video_id' => '$1',
    );

    // YouTube Shorts
    $embed[] = array(
        'name' => 'YouTube Shorts',
        'enabled' => 1,
        'pattern' => 'https?://(?:(?:www|m)\.)?youtube\.com/shorts/([\w-]+)',
        'embedlink' => 'https://www.youtube.com/embed/$1',
        'width' => '640',
        'height' => '360',
        'thumb' => 'https://img.youtube.com/vi/$1/hqdefault.jpg',
        'video_id' => '$1',
    );

    // YouTube - embed URLs (already embedded)
    $embed[] = array(
        'name' => 'YouTube Embed',
        'enabled' => 1,
        'pattern' => 'https?://(?:www\.)?youtube\.com/embed/([\w-]+)',
        'embedlink' => 'https://www.youtube.com/embed/$1',
        'width' => '640',
        'height' => '360',
        'thumb' => 'https://img.youtube.com/vi/$1/hqdefault.jpg',
        'video_id' => '$1',
    );

    // Vimeo
    $embed[] = array(
        'name' => 'Vimeo',
        'enabled' => 1,
        'pattern' => 'https?://(?:www\.)?vimeo\.com/(\d+)',
        'embedlink' => 'https://player.vimeo.com/video/$1',
        'width' => '640',
        'height' => '360',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );

    // Dailymotion - full URL with slug
    $embed[] = array(
        'name' => 'Dailymotion',
        'enabled' => 1,
        'pattern' => 'https?://(?:www\.)?dailymotion\.com/video/([a-z0-9]+)',
        'embedlink' => 'https://www.dailymotion.com/embed/video/$1',
        'width' => '640',
        'height' => '360',
        'thumb' => 'https://www.dailymotion.com/thumbnail/video/$1',
        'video_id' => '$1',
    );

    // Dailymotion - short URL
    $embed[] = array(
        'name' => 'Dailymotion Short',
        'enabled' => 1,
        'pattern' => 'https?://dai\.ly/([a-z0-9]+)',
        'embedlink' => 'https://www.dailymotion.com/embed/video/$1',
        'width' => '640',
        'height' => '360',
        'thumb' => 'https://www.dailymotion.com/thumbnail/video/$1',
        'video_id' => '$1',
    );

    // Facebook Video
    $embed[] = array(
        'name' => 'Facebook Video',
        'enabled' => 1,
        'pattern' => 'https?://(?:www\.)?facebook\.com/(?:.*?)/videos/(\d+)',
        'embedlink' => 'https://www.facebook.com/plugins/video.php?href=https://www.facebook.com/facebook/videos/$1/',
        'width' => '640',
        'height' => '360',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );

    // Twitch - clips
    $embed[] = array(
        'name' => 'Twitch Clip',
        'enabled' => 1,
        'pattern' => 'https?://(?:clips\.twitch\.tv|(?:www\.)?twitch\.tv/\w+/clip)/([\w-]+)',
        'embedlink' => 'https://clips.twitch.tv/embed?clip=$1&parent=localhost',
        'width' => '640',
        'height' => '360',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );

    // Twitch - VODs
    $embed[] = array(
        'name' => 'Twitch VOD',
        'enabled' => 1,
        'pattern' => 'https?://(?:www\.)?twitch\.tv/videos/(\d+)',
        'embedlink' => 'https://player.twitch.tv/?video=$1&parent=localhost',
        'width' => '640',
        'height' => '360',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );

    // Streamable
    $embed[] = array(
        'name' => 'Streamable',
        'enabled' => 1,
        'pattern' => 'https?://(?:www\.)?streamable\.com/([\w]+)',
        'embedlink' => 'https://streamable.com/e/$1',
        'width' => '640',
        'height' => '360',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );

    // Rumble
    $embed[] = array(
        'name' => 'Rumble',
        'enabled' => 1,
        'pattern' => 'https?://(?:www\.)?rumble\.com/embed/([\w]+)',
        'embedlink' => 'https://rumble.com/embed/$1/',
        'width' => '640',
        'height' => '360',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );

    // TikTok
    $embed[] = array(
        'name' => 'TikTok',
        'enabled' => 1,
        'pattern' => 'https?://(?:www\.)?tiktok\.com/@[\w.]+/video/(\d+)',
        'embedlink' => 'https://www.tiktok.com/embed/v2/$1',
        'width' => '340',
        'height' => '700',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );

    // Instagram Reels/Video
    $embed[] = array(
        'name' => 'Instagram',
        'enabled' => 1,
        'pattern' => 'https?://(?:www\.)?instagram\.com/(?:reel|p)/([\w-]+)',
        'embedlink' => 'https://www.instagram.com/p/$1/embed',
        'width' => '400',
        'height' => '500',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );

    // Odysee
    $embed[] = array(
        'name' => 'Odysee',
        'enabled' => 1,
        'pattern' => 'https?://(?:www\.)?odysee\.com/(@[^/]+/[^/?&#]+)',
        'embedlink' => 'https://odysee.com/$/embed/$1',
        'width' => '640',
        'height' => '360',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );

    // Bitchute
    $embed[] = array(
        'name' => 'Bitchute',
        'enabled' => 1,
        'pattern' => 'https?://(?:www\.)?bitchute\.com/video/([\w]+)',
        'embedlink' => 'https://www.bitchute.com/embed/$1/',
        'width' => '640',
        'height' => '360',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );

    // LiveLeak / ItemFix (LiveLeak became ItemFix)
    $embed[] = array(
        'name' => 'ItemFix',
        'enabled' => 1,
        'pattern' => 'https?://(?:www\.)?itemfix\.com/v\?t=([\w-]+)',
        'embedlink' => 'https://www.itemfix.com/v?t=$1',
        'width' => '640',
        'height' => '360',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
    );

    // Direct MP4/WEBM video URLs
    $embed[] = array(
        'name' => 'Direct Video',
        'enabled' => 1,
        'pattern' => '(https?://[^\s<>"]+\.(?:mp4|webm|ogg))',
        'embedlink' => '$1',
        'width' => '640',
        'height' => '360',
        'thumb' => $settings['default_images_url'] . '/video_thumb.jpg',
        'video_id' => '$1',
        'direct_video' => true,
    );

    // Process embed array - match URL against patterns
    foreach ($embed as $arr)
    {
        if (!$arr['enabled'])
            continue;

        $pattern = '#' . $arr['pattern'] . '#i';

        if (preg_match($pattern, $url))
        {
            switch ($type)
            {
                case 1:
                    // Return embed HTML
                    if (!empty($arr['direct_video']))
                    {
                        $video_src = preg_replace($pattern, $arr['embedlink'], $url);
                        return '<video width="' . $arr['width'] . '" height="' . $arr['height'] . '" controls>'
                            . '<source src="' . $video_src . '" type="video/mp4">'
                            . '</video>';
                    }
                    $iframe_src = preg_replace($pattern, $arr['embedlink'], $url);
                    return '<iframe width="' . $arr['width'] . '" height="' . $arr['height'] . '" src="' . $iframe_src . '" frameborder="0" allowfullscreen="true" allow="autoplay; encrypted-media"></iframe>';

                case 2:
                    return preg_replace($pattern, $arr['thumb'], $url);

                case 3:
                    return preg_replace($pattern, $arr['video_id'], $url);

                case 4:
                    return true;

                case 'height':
                    return $arr['height'];

                case 'width':
                    return $arr['width'];
            }
        }
    }

    return false;
}
