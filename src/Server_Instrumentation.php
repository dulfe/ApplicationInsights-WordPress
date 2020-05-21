<?php
namespace ApplicationInsights\WordPress;

/**
 *  Does server-side instrumentation using the PHP SDK for Application Insights
 */
class Server_Instrumentation {
	private $_telemetryClient = null;
	private static $UNTRACKABLE_404;
    private $_isTrack404Enabled;

    public function __construct()
    {
         /* Necessary check for multi-site installation */
        if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }   
        if ( is_multisite() && is_plugin_active_for_network("application-insights/ApplicationInsightsPlugin.php") ) 
        {
            $application_insights_options = get_site_option("applicationinsights_options");
        } else {
            $application_insights_options = get_option("applicationinsights_options");
        } 

        // Only initialize telemetry if we have an Application Insights Key
        if (isset($application_insights_options["instrumentation_key"]) && strlen($application_insights_options["instrumentation_key"]) > 0) {
            $this->_telemetryClient = new \ApplicationInsights\Telemetry_Client();
            $this->_telemetryClient->getContext()->setInstrumentationKey($application_insights_options["instrumentation_key"]);
            $this->_isTrack404Enabled = (isset($application_insights_options['track_404'])) ? ($application_insights_options['track_404'] == '1') : false;
            $sdkVer = $this->_telemetryClient->getContext()->getInternalContext()->getSdkVersion();
            $this->_telemetryClient->getContext()->getInternalContext()->setSdkVersion('wp_' . $sdkVer);

            // Capture Exceptions
            set_exception_handler( array( $this, 'exceptionHandler' ) );
        }
	}

    function endRequest()
    {
        // Only send telemetry is the telemetry client was created
        if ($this->_telemetryClient != null) {
            if (is_page() || is_single() || is_category() || is_home() || is_archive() || $this->isTrackable404() )
            {
                $url = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
                $requestName = Server_Instrumentation::getPageTitle();
                $startTime = $_SERVER["REQUEST_TIME"];
                $duration = floatval(timer_stop(0, 3)) * 1000;
                $this->_telemetryClient->trackRequest($requestName, $url, $startTime, $duration, http_response_code(), !is_404());

                // Flush all telemetry items
                $this->_telemetryClient->flush();
            }
        }
	}

    function exceptionHandler($exception)
    {
        if ($exception != NULL)
        {
            $this->_telemetryClient->trackException($exception);
            $this->_telemetryClient->flush();
        }
    }

	function isTrackable404() {
		$return = false;

		if ( $this->_isTrack404Enabled && is_404() ) {
			$return = ! in_array( $_SERVER['REQUEST_URI'], $this->getUntrackableFiles() );
		}

		return $return;
	}

	function getUntrackableFiles() {
		if ( Server_Instrumentation::$UNTRACKABLE_404 == null ) {
			Server_Instrumentation::$UNTRACKABLE_404 = Common::getDefaultUntrackable404Files();
		}

		return Server_Instrumentation::$UNTRACKABLE_404;
    }

     /**
     * Returns the current WordPress Page Title using the same rules as the client side,
     * but it tries to "undo" the esc_html encoding.
     * We use this because we want the reports from the "server side" to have the same names
     * as the "client side" (or as close as possble)
     * 
     * @return string Page Title
     */
    public static function getPageTitle() {
        $title = wp_get_document_title();

        return html_entity_decode($title);
    }
    
}
