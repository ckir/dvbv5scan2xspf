<?php
//
// Add xmlwriter extension to your installation
// sudo apt-get install php7.2-xmlwriter
// Documentation at https://wiki.videolan.org/Documentation:Streaming_HowTo/Stream_a_DVB_Channel/
//
//
// Usage: php dvbv5scan2xspf.php -f dvbchannels.conf > dvbchannels.xspf
//

error_reporting(E_ERROR | E_PARSE);
if (! extension_loaded("xmlwriter")) {
    die("Extension xmlwriter is not available" . PHP_EOL . "try sudo apt-get install php7.2-xmlwriter" . PHP_EOL);
}

$options = getopt("f:");
if ((! $options) || (! $options['f'])) {
    $channels_file = "dvbchannels.conf";
} else {
    $channels_file = $options['f'];
}

$channels = parse_ini_file($channels_file, true);
if(! is_array($channels)) {
    die("Problem reading $channels_file" . PHP_EOL);
}

$xw = xmlwriter_open_memory();
xmlwriter_set_indent($xw, 4);
$res = xmlwriter_set_indent_string($xw, ' ');

xmlwriter_start_document($xw, '1.0', 'UTF-8');

xmlwriter_start_element($xw, 'playlist');
xmlwriter_start_attribute($xw, 'version');
xmlwriter_text($xw, '1');
xmlwriter_start_attribute($xw, 'xmlns');
xmlwriter_text($xw, 'http://xspf.org/ns/0/');
xmlwriter_start_attribute($xw, 'xmlns:vlc');
xmlwriter_text($xw, 'http://www.videolan.org/vlc/playlist/ns/0/');
xmlwriter_end_attribute($xw);

xmlwriter_start_element($xw, 'title');
xmlwriter_text($xw, 'DVB-S Playlist');
xmlwriter_end_element($xw); // title

xmlwriter_start_element($xw, 'trackList');

$index = 1;
foreach ($channels as $channel => $data) {
    xmlwriter_start_element($xw, 'track');

        xmlwriter_start_element($xw, 'title');
        xmlwriter_text($xw, $channel);
        xmlwriter_end_element($xw); // title

        xmlwriter_start_element($xw, 'location');
        $frequency = $data['FREQUENCY'];
        xmlwriter_text($xw, "dvb-t://frequency=$frequency");
        xmlwriter_end_element($xw); // location

        xmlwriter_start_element($xw, 'extension');

            xmlwriter_start_attribute($xw, 'application');
            xmlwriter_text($xw, "http://www.videolan.org/vlc/playlist/0");
            xmlwriter_end_attribute($xw); // application

            xmlwriter_start_element($xw, 'vlc:option');
            $dvb_bandwidth = substr($data['TRANSMISSION_MODE'], 0, 1);
            xmlwriter_text($xw, "dvb-bandwidth=$dvb_bandwidth");
            xmlwriter_end_element($xw); // vlc:option

            xmlwriter_start_element($xw, 'vlc:option');
            $dvb_ts_id= 300;
            xmlwriter_text($xw, "dvb-ts-id=$dvb_ts_id");
            xmlwriter_end_element($xw); // vlc:option

            xmlwriter_start_element($xw, 'vlc:option');
            $dvb_code_rate_hp = $data['CODE_RATE_HP'];
            xmlwriter_text($xw, "dvb-code-rate-hp=$dvb_code_rate_hp");
            xmlwriter_end_element($xw); // vlc:option

            xmlwriter_start_element($xw, 'vlc:option');
            $dvb_modulation = $data['MODULATION'];
            xmlwriter_text($xw, "dvb-modulation=$dvb_modulation");
            xmlwriter_end_element($xw); // vlc:option

            xmlwriter_start_element($xw, 'vlc:option');
            $dvb_transmission = substr($data['TRANSMISSION_MODE'], 0, 1);
            xmlwriter_text($xw, "dvb-transmission=$dvb_transmission");
            xmlwriter_end_element($xw); // vlc:option

            xmlwriter_start_element($xw, 'vlc:option');
            $dvb_guard = $data['GUARD_INTERVAL'];
            xmlwriter_text($xw, "dvb-guard=$dvb_guard");
            xmlwriter_end_element($xw); // vlc:option

            xmlwriter_start_element($xw, 'vlc:id');
                xmlwriter_text($xw, sprintf("% 04d", $index++));
            xmlwriter_end_element($xw); // VLC:id

            xmlwriter_start_element($xw, 'vlc:option');
            $program = $data['SERVICE_ID'];;
            xmlwriter_text($xw, "program=$program");
            xmlwriter_end_element($xw); // vlc:option

        xmlwriter_end_element($xw); // extension

    xmlwriter_end_element($xw); // track
}

xmlwriter_end_element($xw); // track list

xmlwriter_end_element($xw); // playlist
xmlwriter_end_document($xw);

echo xmlwriter_output_memory($xw);
