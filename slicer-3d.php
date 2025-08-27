<?php
//
/**
 * @package  EChatBotPlugin
 */
/*
 * Plugin Name: slicer-3d
 * Description: Stl to Gcode Slicer
 * Version: 1.0.0
 * Author: 3dgarage
 * Author URI: http://www.3dgarage.tech	
 * License: 
 * Text Domain: slicer-3d-plugin
 * 
 * Copyright (c) 2025 [Tomer Shotland / 3dgarage]

 * All rights reserved.

 * This software and its source code are proprietary and confidential.
 * Unauthorized copying, distribution, modification, or use of this software,
 * in whole or in part, is strictly prohibited without the express written
 * permission of the author.

 * For licensing inquiries, contact: tomershotland@gmail.com

/**
 * Initialize all the core classes of the plugin
 */

require_once __DIR__ . '/./init.php';

// If this file is called directly, abort!!!
defined('ABSPATH') or die('No direct access form the folder !');

// Conditional asset loading
add_action( 'wp', function() {
    if ( is_singular() && has_shortcode( get_post()->post_content, 'slicer_3d_shortcode' ) ) {
        add_action( 'wp_enqueue_scripts', 'slicer_3d_enqueue_assets', 20);
    }
});

function slicer_3d_enqueue_assets()
{
    $plugin_url = plugin_dir_url(__FILE__);

    // CSS
    wp_enqueue_style('slicer-3d-style', $plugin_url . 'assets/css/style.css?v=' . time());

    // JS - external libraries
    wp_enqueue_script('axios', 'https://cdnjs.cloudflare.com/ajax/libs/axios/1.10.0/axios.min.js', [], null, true);
    wp_enqueue_script('three-js', 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js', [], null, true);

    // JS - plugin main script
    wp_enqueue_script('slicer-3d-index', $plugin_url . 'assets/js/index.js?v=' . time(), ['jquery', 'axios', 'three-js'], null, true);
}

add_shortcode('slicer_3d_shortcode', 'slicer_3d_render_front');

function slicer_3d_render_front()
{
    $image_url = plugins_url('assets/images/loading.gif?v=' . time(), __FILE__);

    ob_start();
?>

    <div class="slicer-container">
        <div class="app-container">
            <div class="sidebar">
                <h1 style="font-size: 31px !important;"><b>Online 3D Slicer</b></h1><h6>Version Pre-Alpha</h6>

                <!-- File Upload Section -->
                <div class="section">
                    <!--h3>📁 File Upload</h3-->
                    <div class="file-upload" id="fileUpload">
                        <input
                            type="file"
                            id="fileInput"
                            class="file-input"
                            accept=".stl,.obj,.3mf,.gcode,.g"
                            multiple />
                        <div>
                            <div style="font-size: 2em; margin-bottom: 10px">📎</div>
                            <div><strong>Click or drag your STL file here</strong></div>
                            <!-- <div style="font-size: 12px; color: #666; margin-top: 5px">
                                Supports STL, OBJ, 3MF, G-code files
                            </div> -->
                        </div>
                    </div>
                    <!-- <div class="demo-models">
                    <button class="demo-btn" data-shape="cube">Load Cube</button>
                    <button class="demo-btn" data-shape="sphere">Load Sphere</button>
                    <button class="demo-btn" data-shape="cylinder">Load Cylinder</button>
                    <button class="demo-btn" data-shape="torus">Load Torus</button>
                </div> -->
                    <div
                        style="
                            margin-top: 10px;
                            padding: 8px;
                            background: #f0f8ff;
                            border-radius: 4px;
                            font-size: 11px;
                            color: #666;
                        ">
                        <!-- 💡 <strong>Tip:</strong> Upload STL/OBJ files to slice, or upload G-code
                        files to visualize existing toolpaths -->
                    </div>
                  <button class="xyz-preset-btn" id="dropToBed" style="width:115px;"> Drop to Bed</button>
                  <button class="xyz-preset-btn" id="resetPosition">Reset Position</button>
                </div>
<div style="display: grid; grid-template-columns: 1fr auto; gap: 15px; align-items: center;">
                        <input type="range" id="scaleSlider" min="0.1" max="5" step="0.1" value="1" 
                               style="width: 100%; accent-color: #0ea5e9;">
                        <span id="scaleValue" style="font-weight: bold; color: #075985; min-width: 50px; text-align: center;">1.0x</span>
                    </div>
                <!-- Print Settings -->
                <div class="section">
                    <h3>⚙️ Print Settings</h3>

                    <!-- Preset Buttons -->
                    <div class="preset-buttons">
                        <div class="preset-btn" data-preset="draft">🚀 Draft</div>
                        <div class="preset-btn active" data-preset="normal">⚡ Normal</div>
                        <div class="preset-btn" data-preset="fine">✨ Fine</div>
                        <div class="preset-btn" data-preset="custom">🔧 Custom</div>
                    </div>

                    <!-- Setting Tabs -->
                    <div class="setting-tabs">
                        <button class="tab-btn active" data-tab="basic-tab">Basic</button>
                        <button class="tab-btn" data-tab="advanced-tab">Advanced</button>
                        <button class="tab-btn" data-tab="material-tab">Material</button>
                    </div>

                    <!-- Basic Settings -->
                    <div class="tab-content active" id="basic-tab">
                        <!-- <div class="setting-group1">
                            <label for="layerHeight">Bed Size<BR></label><label for="layerHeight"></label><br>X<br>&nbsp;<br>
                            <input type="number" id="bedSizeX" value="260" min="0.05" max="1000" step="1" style="width:62px;" class="preset-btn"><label for="layerHeight">&nbsp;Y<br>&nbsp;</label>
                            <input type="number" id="bedSizeY" value="260" min="0.05" max="1000" step="1" style="width:62px;" class="preset-btn">
                            <label for="layerHeight">&nbsp;Z&nbsp;</label>
                            <input type="number" id="bedSizeZ" value="260" min="0.05" max="1000" step="1" style="width:62px;" class="preset-btn">
                        </div><br> -->
                        <div class="setting-group" style="width:85px;float:left;padding:5px;">
                            <label for="diameter">X</label>
                            <input type="number" id="bedSizeX" value="260" min="0.05" max="1000" step="1">
                        </div>
                        <div class="setting-group" style="width:85px;float:left;padding:5px;">
                            <label for="diameter">Y</label>
                            <input type="number" id="bedSizeY" value="260" min="0.05" max="1000" step="1">
                        </div>
                         <div class="setting-group" style="width:85px;float:left;padding:5px;">
                            <label for="diameter">Z</label>
                            <input type="number" id="bedSizeZ" value="260" min="0.05" max="1000" step="1">
                        </div>
                        
 <div class="setting-group" style="width:85px;float:left;padding:5px;">
                            <label for="diameter">Diameter of thickness</label>
                            <input type="number" id="diameter" value="0.4" min="0.05" max="1.0" step="1">
                        </div>
                         

                        <div class="setting-group" style="width:85px;float:left;padding:5px;">
                            <label for="layerHeight">Layer Height (mm)</label>
                            <input
                                type="number"
                                id="layerHeight"
                                value="0.2"
                                min="0.05"
                                max="1.0"
                                step="0.05" />
                        </div>
                        <div class="setting-group" style="width:85px;float:left;padding:5px;">
                            <label for="infillDensity">Infill Density (%)</label>
                            <input
                                type="number"
                                id="infillDensity"
                                value="20"
                                min="0"
                                max="100"
                                step="5" />
                        </div>
                        <div class="setting-group" style="width:85px;float:left;padding:5px;">
                            <label for="printSpeed">Print Speed (mm/s)</label>
                            <input
                                type="number"
                                id="printSpeed"
                                value="60"
                                min="10"
                                max="150"
                                step="5" />
                        </div>
                        <div class="setting-group" style="width:167px;float:left;">
                            <label for="infillPattern">Infill Pattern(pattern Type)</label>
                            <select id="infillPattern">
                                <option value="grid"  >Grid</option>
                                <option value="lines">Lines</option>
                                <option value="triangular">Triangular</option>
                                <option value="honeycomb">Honeycomb</option>
                                <option value="gyroid">Gyroid</option>
                            </select>
                        </div>
						
                    </div>

                    <!-- Advanced Settings -->
                    <div class="tab-content" id="advanced-tab">
                        <div class="advanced-settings">
                            <div class="setting-group">
                                <label for="wallThickness">Wall Thickness (mm)</label>
                                <input
                                    type="number"
                                    id="wallThickness"
                                    value="1.2"
                                    min="0.4"
                                    max="5"
                                    step="0.1" />
                            </div>
                            <div class="setting-group">
                                <label for="topBottomLayers">Top/Bottom Layers</label>
                                <input
                                    type="number"
                                    id="topBottomLayers"
                                    value="3"
                                    min="1"
                                    max="10"
                                    step="1" />
                            </div>
                            <div class="setting-group">
                                <label for="supportEnabled">Enable Supports</label>
                                <select id="supportEnabled">
                                    <option value="false">Disabled</option>
                                    <option value="true">Enabled</option>
                                    <option value="touching">Touching Buildplate</option>
                                </select>
                            </div>
                            <div class="setting-group">
                                <label for="supportDensity">Support Density (%)</label>
                                <input
                                    type="number"
                                    id="supportDensity"
                                    value="15"
                                    min="5"
                                    max="50"
                                    step="5" />
                            </div>
                            <div class="setting-group">
                                <label for="retraction">Retraction (mm)</label>
                                <input
                                    type="number"
                                    id="retraction"
                                    value="4.5"
                                    min="0"
                                    max="10"
                                    step="0.1" />
                            </div>
                            <div class="setting-group">
                                <label for="travelSpeed">Travel Speed (mm/s)</label>
                                <input
                                    type="number"
                                    id="travelSpeed"
                                    value="120"
                                    min="50"
                                    max="300"
                                    step="10" />
                            </div>
                        </div>
                    </div>

                    <!-- Material Settings -->
                    <div class="tab-content" id="material-tab">
                        <div class="setting-group">
                            <label for="material">Material</label>
                            <select id="material">
                                <option value="pla">PLA</option>
                                <option value="abs">ABS</option>
                                <option value="petg">PETG</option>
                                <option value="tpu">TPU</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div class="setting-group">
                            <label for="nozzleTemp">Nozzle Temperature (°C)</label>
                            <input
                                type="number"
                                id="nozzleTemp"
                                value="200"
                                min="150"
                                max="300"
                                step="5" />
                        </div>
                        <div class="setting-group">
                            <label for="bedTemp">Bed Temperature (°C)</label>
                            <input
                                type="number"
                                id="bedTemp"
                                value="60"
                                min="0"
                                max="120"
                                step="5" />
                        </div>
                        <div class="setting-group">
                            <label for="fanSpeed">Fan Speed (%)</label>
                            <input
                                type="number"
                                id="fanSpeed"
                                value="100"
                                min="0"
                                max="100"
                                step="5" />
                        </div>
                    </div>

                    <!-- Action Buttons --><div id="fileInfo"></div>
                    <div style="margin-top: 20px;position: sticky;background-color: #ffffff;
    bottom: 0;">
                        <div class="setting-group" >
						 
						 <!-- <table style="border:0;" border="0"><tr><td><input type="checkbox" id="agreement" name="scales" checked /></td><td><label for="scales">I Agree to terms</label></td><td><a ="https://www.3dgarage.tech/terms-of-use/">Terms of service</a></td></tr></table> -->
</div>                   <div style="float:left;"><input type="checkbox" id="agreement" name="scales" unchecked /></div><div  style="float:left;">I agree to<a href="https://www.3dgarage.tech/terms-of-use/">Terms of service</a></div>
                         <button
                           class="btn btn-success"
                            id="downloadBtn"
                            style="width: 100%;"
                            style="width: 100%;display:none; margin-bottom:10px"
                            onclick="checkCheckBox()">
                            💾 Download G-code
                        </button>
                         <div class="log-area" id="logArea"></div>
                        <button
                            class="btn btn-success"
                            id="downloadBtn2"
                            style="width: 100%;opacity:0;
                            ">
                            💾 Download G-code
                        </button>
                    </div>
  <!-- Enhanced XYZ Position Controls -->
            <div class="xyz-controls">
                <h3>🎯 Position Controls</h3>
                <div class="xyz-grid">
                    <div class="xyz-control">
                        <label for="posX">X Position (mm)</label>
                        <input type="number" id="posX" value="0" step="0.1" min="-500" max="500">
                    </div>
                    <div class="xyz-control">
                        <label for="posY">Y Position (mm)</label>
                        <input type="number" id="posY" value="0" step="0.1" min="-500" max="500">
                    </div>
                    <div class="xyz-control">
                        <label for="posZ">Z Position (mm)</label>
                        <input type="number" id="posZ" value="0" step="0.1" min="-500" max="500">
                    </div>
                </div>
                
                <!-- <div class="xyz-presets">
                    <button class="xyz-preset-btn" id="centerModel">📍 Center Model</button>
                    <button class="xyz-preset-btn" id="resetPosition">🏠 Reset Position</button>
                    <button class="xyz-preset-btn" id="moveToOrigin">⚡ Move to Origin</button>
                    <button class="xyz-preset-btn" id="dropToBed">⬇️ Drop to Bed</button>
                </div> -->

                <!-- Scale Controls -->
                <div style="border-top: 2px solid #0ea5e9; padding-top: 15px; margin-top: 15px;">
                    <label style="display: block; margin-bottom: 10px; font-weight: bold; color: #075985;">🔍 Scale Factor</label>
                    <div style="display: grid; grid-template-columns: 1fr auto; gap: 15px; align-items: center;">
                        <input type="range" id="scaleSlider" min="0.1" max="5" step="0.1" value="1" 
                               style="width: 100%; accent-color: #0ea5e9;">
                        <span id="scaleValue" style="font-weight: bold; color: #075985; min-width: 50px; text-align: center;">1.0x</span>
                    </div>
                    <button class="xyz-preset-btn" id="resetScale" style="width: 100%; margin-top: 10px;">↺ Reset Scale</button>
                </div>

                <!-- Rotation Controls -->
                <div style="border-top: 2px solid #0ea5e9; padding-top: 15px; margin-top: 15px;">
                    <label style="display: block; margin-bottom: 10px; font-weight: bold; color: #075985;">🔄 Rotation (90° steps)</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        <button class="xyz-preset-btn" id="rotateX">↻ Rotate X</button>
                        <button class="xyz-preset-btn" id="rotateY">↻ Rotate Y</button>
                        <button class="xyz-preset-btn" id="rotateZ">↻ Rotate Z</button>
                    </div>
                </div>
            </div>
                    <!-- Progress -->
                    <div class="progress-container" id="progressContainer">
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                        <div class="progress-text" id="progressText">Processing...</div>
                    </div>

                    <!-- Log Area -->
                   
                </div>
            </div>

            <div class="main-content">
                <!-- Toolbar -->
                <div class="toolbar">
                    <div class="toolbar-group">
                        <button class="toolbar-btn active" id="viewSolid">🧊 Solid</button>
                        <button class="toolbar-btn" id="viewWireframe">📐 Wireframe</button>
                        <button class="toolbar-btn" id="viewLayers">📄 Layers</button>
                        <button class="toolbar-btn" id="viewGcode">🛣️ G-code</button>
                        <button class="toolbar-btn" id="viewSupports">🏗️ Supports</button>
                    </div>
                    <div class="toolbar-group">
                        <button class="toolbar-btn" id="resetView">🎯 Reset View</button>
                        <button class="toolbar-btn" id="fitView">📏 Fit to View</button>
                        <button class="toolbar-btn" id="toggleAxes">⚡ Axes</button>
                    </div>
                </div>

                <!-- 3D Viewer -->
                <div class="viewer-area">
                    <div id="viewer3d"></div>

                    <!-- Model Info Panel -->
                    <div class="model-info" id="modelInfo">
                        <h4>Model Information</h4>
                        <div>Vertices: <span id="vertexCount">0</span></div>
                        <div>Faces: <span id="faceCount">0</span></div>
                        <div>Size: <span id="modelSize">0×0×0 mm</span></div>
                        <div>Volume: <span id="modelVolume">0 cm³</span></div>
                        <div>Estimated Print Time: <span id="printTime">--</span></div>
                        <div>Material Usage: <span id="materialUsage">-- g</span></div>
                    </div>

                    <!-- Layer Preview -->
                    <div class="layer-preview" id="layerPreview">
                        <h4>Layer Preview</h4>
                        <div>
                            Layer: <span id="currentLayer">0</span> /
                            <span id="totalLayers">0</span>
                        </div>
                        <input
                            type="range"
                            class="layer-slider"
                            id="layerSlider"
                            min="0"
                            max="0"
                            value="0" />
                        <div>Height: <span id="layerHeight">0.00</span> mm</div>
                    </div>

                    <!-- G-code Viewer Controls -->
                    <div class="layer-preview" id="gcodeViewer" style="display: none">
                        <h4>G-code Viewer</h4>
                        <div style="display: flex; gap: 10px; margin-bottom: 10px">
                            <button class="toolbar-btn" id="playGcode">▶️ Play</button>
                            <button class="toolbar-btn" id="pauseGcode">⏸️ Pause</button>
                            <button class="toolbar-btn" id="resetGcode">⏹️ Reset</button>
                        </div>
                        <div>
                            Layer: <span id="gcodeCurrentLayer">0</span> /
                            <span id="gcodeTotalLayers">0</span>
                        </div>
                        <input
                            type="range"
                            class="layer-slider"
                            id="gcodeLayerSlider"
                            min="0"
                            max="0"
                            value="0" />
                        <div>Progress: <span id="gcodeProgress">0</span>%</div>
                        <div>
                            Speed:
                            <select id="playbackSpeed" style="width: 60px; padding: 2px">
                                <option value="0.25">0.25x</option>
                                <option value="0.5">0.5x</option>
                                <option value="1" selected>1x</option>
                                <option value="2">2x</option>
                                <option value="5">5x</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Status Bar -->
                <div class="status-bar">
                    <div id="statusLeft">Ready</div>
                    <div id="statusRight">No model loaded</div>
                </div>
            </div>
        </div>

        <div id="loadingModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background-color:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
            <div style="background:white; padding:20px; border-radius:8px;">
                <img src="<?php echo esc_url($image_url) ?>" alt="loading data" style="width:40px; height:40px; display:block; margin:0 auto 10px;" />
                <span><marquee width="300px" direction="left" height="30px">
Converting file - Please wait. 3D printing (also called additive manufacturing) was invented in 1983 by Chuck Hull.1989 – FDM (Fused Deposition Modeling) is developed and patented by Scott Crump, founder of Stratasys.
 </span>
</marquee>
<br>
<a href="javascript:displayMessage('Hide Conversion Screen')">Hide Conversion Screen</a>

            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}

// add_action('template_redirect', 'slicer_3d_render_shortcode_in_blank_page');

function slicer_3d_render_shortcode_in_blank_page()
{
    global $post;

    if (!is_singular() || !has_shortcode($post->post_content, 'slicer_3d_shortcode')) {
        return;
    }

    status_header(200);
    header('Content-Type: text/html');

    echo '<!DOCTYPE html><html><head><title>slicer-3d</title>';
    wp_head();
    echo '</head><body>';

    echo do_shortcode('[slicer_3d_shortcode]');

    wp_footer();
    echo '</body></html>';

    exit;
}

add_filter('upload_mimes', 'slicer_3d_allow_custom_mimes');
function slicer_3d_allow_custom_mimes($mimes)
{
    $mimes['stl'] = 'application/sla'; // או 'model/stl' לפי הצורך
    return $mimes;
}


add_action('rest_api_init', 'slicer_3d_register_api_routes');

function slicer_3d_register_api_routes()
{
    register_rest_route('slicer-3d/v1', '/submit', [
        'methods'  => 'POST',
        'callback' => 'slicer_3d_api_submit',
        'permission_callback' => '__return_true', // Still public
    ]);
}

function slicer_3d_api_submit($request)
{
    // validate request post & files data
    slicer_3d_validate_data();

    // get data from POST
    $bed_size_x = floatval($request->get_param('bed_size_x'));
    $bed_size_y = floatval($request->get_param('bed_size_y'));
    $bed_size_z = floatval($request->get_param('bed_size_z'));
    $diameter = floatval($request->get_param('diameter'));
    $layer_height = floatval($request->get_param('layer_height'));
    $infill_density = floatval($request->get_param('infill_density'));
    $print_speed = floatval($request->get_param('print_speed'));
    $infill_pattern = strtolower($request->get_param('infill_pattern'));
    $wall_thickness = floatval($request->get_param('wall_thickness'));
    $top_bottom_layers = floatval($request->get_param('top_bottom_layers'));
    $support_enabled = strtolower($request->get_param('support_enabled'));
    $support_density = floatval($request->get_param('support_density'));
    $retraction = floatval($request->get_param('retraction'));
    $travel_speed = floatval($request->get_param('travel_speed'));
    $material = strtolower($request->get_param('material'));
    $nozzle_temp = floatval($request->get_param('nozzle_temp'));
    $bed_temp = floatval($request->get_param('bed_temp'));
    $fan_speed = floatval($request->get_param('fan_speed'));

    // Get file data using WordPress functions
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    $uploadedfile = $_FILES['stl_file'];
    $upload_overrides = ['test_form' => false];

    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

    if ($movefile && !isset($movefile['error'])) {
        $fileTmpPath = $movefile['file'];
        $fileName = basename($movefile['file']);

        // Set environment (local/production) for slicer binary
        $env = defined('SLICER_3D_WP_ENV') ? SLICER_3D_WP_ENV : 'production';
        $bin_dir = plugin_dir_path(__FILE__) . 'bin' . DIRECTORY_SEPARATOR;

        if ($env === 'local') {
            $slicer = $bin_dir . 'superslicer_console.exe';
        } else {
            $slicer = $bin_dir . 'squashfs-root' . DIRECTORY_SEPARATOR . 'superslicer';
        }

        // // Create temp files
        // $tmpStl = wp_tempnam($fileName, sys_get_temp_dir()) . '.stl';
        // $tmpGcode = wp_tempnam('gcode', sys_get_temp_dir()) . '.gcode';

        // error_log("-- LOG file names --\n" . 'fileTmpPath: ' . $fileTmpPath .
        //     '; fileName: ' . $fileName . '; tmpStl: ' . $tmpStl . '; tmpGcode: ' . $tmpGcode);

        // Define project temp directory
        $projectTempDir = __DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;

        // Create the directory if it doesn't exist
        if (!file_exists($projectTempDir)) {
            mkdir($projectTempDir, 0755, true); // recursive mkdir
        }

        // Create temp file paths in project dir
        $tmpStl = wp_tempnam($fileName, $projectTempDir) . '.stl';
        $tmpGcode = wp_tempnam('gcode', $projectTempDir) . '.gcode';

        error_log("-- LOG file names --" .
            "\nfileTmpPath: " . $fileTmpPath .
            "\nfileName: " . $fileName .
            "\ntmpStl: " . $tmpStl .
            "\ntmpGcode: " . $tmpGcode);

        // Move uploaded STL to temp location
        if (copy($fileTmpPath, $tmpStl)) {
            // if (move_uploaded_file($fileTmpPath, $tmpStl)) {
            $cmd = escapeshellcmd($slicer) .
                " --export-gcode" .
                " --scale-to-fit " . escapeshellarg("$bed_size_x,$bed_size_y,$bed_size_z") .
                " --nozzle-diameter " . escapeshellarg($diameter) .
                " --layer-height " . escapeshellarg($layer_height) .
                " --fill-density " . escapeshellarg($infill_density) .
                " --infill-speed " . escapeshellarg($print_speed) .
                " --fill-pattern " . escapeshellarg($infill_pattern) .
                " --perimeters " . escapeshellarg($wall_thickness) .
                " --bottom-solid-layers " . escapeshellarg($top_bottom_layers) .
                " --support-tree-top-rate " . escapeshellarg($support_density) .
                " --retract-length-toolchange " . escapeshellarg($retraction) .
                " --travel-speed " . escapeshellarg($travel_speed) .
                " --filament-type " . escapeshellarg($material) .
                " --first-layer-temperature " . escapeshellarg($nozzle_temp) .
                " --bed-temperature " . escapeshellarg($bed_temp) .
                " --external-perimeter-fan-speed " . escapeshellarg($fan_speed) .
                " --output " . escapeshellarg($tmpGcode) . " " . escapeshellarg($tmpStl) .
                ($support_enabled === "true" ? " --support-material" : ($support_enabled === "touching" ? " --support-material-buildplate-only" : ""));

            // Execute slicer
            $output = [];
            $status = 0;
            exec($cmd . ' 2>&1', $output, $status);

            if ($status === 0 && file_exists($tmpGcode)) {
                $originalName = pathinfo($fileName, PATHINFO_FILENAME);
                $safeName = sanitize_file_name($originalName);
                $gcodeFilename = $safeName . '.gcode';
                $zipFilename = $safeName . '.zip';
                $tmpZip = wp_tempnam('gcode_zip', $projectTempDir) . '.zip';
  

                // Create ZIP file
                $zip = new ZipArchive();
                if ($zip->open($tmpZip, ZipArchive::CREATE) !== TRUE) {
                    wp_die(__('Error creating ZIP file', 'slicer-3d-plugin'), '', ['response' => 500]);
                }
                $zip->addFile($tmpGcode, $gcodeFilename);
                $zip->close();
             

                // Clean output buffer
                if (ob_get_level()) {
                    ob_end_clean();
                }

                // Send file to browser
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
                header('Content-Length: ' . filesize($tmpZip));

                $fp = fopen($tmpZip, 'rb');
                while (!feof($fp)) {
                    echo fread($fp, 8192);
                    flush();
                }
                fclose($fp);

                register_shutdown_function(function () use ($tmpStl, $tmpGcode, $tmpZip) {
                    // Cleanup temp files
                    foreach ([$tmpStl, $tmpGcode, $tmpZip] as $fileName) {
                        $fileNameWithoutExt = slicer_3d_remove_file_extension($fileName);
                        @unlink($fileName);
                        @unlink($fileNameWithoutExt);
                                        

                    }
                });

                exit;
            } else {
                slicer_3d_log_and_exit(__('Slicing failed with command: ' . $cmd . "\nOutput:\n" . implode("\n", $output), 'slicer-3d-plugin'), 500);
            }
        } else {
            slicer_3d_log_and_exit(__('Failed to move uploaded STL file.', 'slicer-3d-plugin'), 500);
        }
    } else {
        slicer_3d_log_and_exit(__('File upload failed: ' . $movefile['error'], 'slicer-3d-plugin'), 400);
    }
}

function slicer_3d_remove_file_extension(string $filename): string
{
    $lastDot = strrpos($filename, '.');
    if ($lastDot !== false) {
        return substr($filename, 0, $lastDot);
    }
    return $filename; // No extension found
}

function slicer_3d_validate_data()
{
    if (!isset($_FILES['stl_file'])) {
        slicer_3d_log_and_exit(__('No STL file uploaded.', 'slicer-3d-plugin'), 400);
    }

    if ($_FILES['stl_file']['error'] !== UPLOAD_ERR_OK) {
        slicer_3d_log_and_exit(__('File upload error: ', 'slicer-3d-plugin') . $_FILES['stl_file']['error'], 400);
    }

    $fileName = $_FILES['stl_file']['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($fileExt !== 'stl') {
        slicer_3d_log_and_exit(__('Invalid file extension. STL required.', 'slicer-3d-plugin'), 400);
    }

    $required_numeric = [
        'bed_size_x',
        'bed_size_y',
        'bed_size_z',
        'diameter',
        'layer_height',
        'infill_density',
        'print_speed',
        'wall_thickness',
        'top_bottom_layers',
        'support_density',
        'retraction',
        'travel_speed',
        'nozzle_temp',
        'bed_temp',
        'fan_speed'
    ];

    foreach ($required_numeric as $param) {
        if (!isset($_POST[$param]) || $_POST[$param] === '') {
            slicer_3d_log_and_exit(sprintf(__('Missing %s parameter.', 'slicer-3d-plugin'), $param), 400);
        }
        if (!is_numeric($_POST[$param])) {
            slicer_3d_log_and_exit(sprintf(__('Invalid %s parameter.', 'slicer-3d-plugin'), $param), 400);
        }
    }

    if (
        floatval($_POST['infill_density']) < 0 ||
        floatval($_POST['infill_density']) > 100
    ) {
        slicer_3d_log_and_exit(__('Invalid infill density parameter.', 'slicer-3d-plugin'), 400);
    }

    if (
        floatval($_POST['fan_speed']) < 0 ||
        floatval($_POST['fan_speed']) > 100
    ) {
        slicer_3d_log_and_exit(__('Invalid fan speed parameter.', 'slicer-3d-plugin'), 400);
    }

    $allowedInfillPatterns = ['grid', 'lines', 'triangular', 'honeycomb', 'gyroid'];
    if (
        !isset($_POST['infill_pattern']) ||
        !in_array(strtolower($_POST['infill_pattern']), $allowedInfillPatterns)
    ) {
        slicer_3d_log_and_exit(__('Invalid infill pattern parameter.', 'slicer-3d-plugin'), 400);
    }

    $allowedSupportEnabledPatterns = ['false', 'true', 'touching'];
    if (
        !isset($_POST['support_enabled']) ||
        !in_array(strtolower($_POST['support_enabled']), $allowedSupportEnabledPatterns)
    ) {
        slicer_3d_log_and_exit(__('Invalid support enabled parameter.', 'slicer-3d-plugin'), 400);
    }

    $allowedMaterialPatterns = ['pla', 'abs', 'petg', 'tpu', 'custom'];
    if (
        !isset($_POST['material']) ||
        !in_array(strtolower($_POST['material']), $allowedMaterialPatterns)
    ) {
        slicer_3d_log_and_exit(__('Invalid material parameter.', 'slicer-3d-plugin'), 400);
    }
}

function slicer_3d_log_and_exit($message, $code)
{
    if (function_exists('error_log')) {
        error_log("Validation error: " . $message . "\n" . print_r($_POST, true) . "\n" . print_r($_FILES, true));
    }
    status_header($code);
    wp_send_json_error(['message' => $message], $code);
    exit;
}
