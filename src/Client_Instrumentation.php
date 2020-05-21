<?php
namespace ApplicationInsights\WordPress;

/**
 * Does client-side instrumentation using the Javascript SDK for Application Insights
 */
class Client_Instrumentation
{
   function addPrefix() {

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

        /* Get current KEY */
        $aiKey = $application_insights_options["instrumentation_key"];

        /* Application Insights Javascript Code @ as of 2020-05-21 */
        $rawSnippet = '<script type="text/javascript">
            var sdkInstance="appInsightsSDK";window[sdkInstance]="appInsights";var aiName=window[sdkInstance],aisdk=window[aiName]||function(e){function n(e){t[e]=function(){var n=arguments;t.queue.push(function(){t[e].apply(t,n)})}}var t={config:e};t.initialize=!0;var i=document,a=window;setTimeout(function(){var n=i.createElement("script");n.src=e.url||"https://az416426.vo.msecnd.net/scripts/b/ai.2.min.js",i.getElementsByTagName("script")[0].parentNode.appendChild(n)});try{t.cookie=i.cookie}catch(e){}t.queue=[],t.version=2;for(var r=["Event","PageView","Exception","Trace","DependencyData","Metric","PageViewPerformance"];r.length;)n("track"+r.pop());n("startTrackPage"),n("stopTrackPage");var s="Track"+r[0];if(n("start"+s),n("stop"+s),n("setAuthenticatedUserContext"),n("clearAuthenticatedUserContext"),n("flush"),!(!0===e.disableExceptionTracking||e.extensionConfig&&e.extensionConfig.ApplicationInsightsAnalytics&&!0===e.extensionConfig.ApplicationInsightsAnalytics.disableExceptionTracking)){n("_"+(r="onerror"));var o=a[r];a[r]=function(e,n,i,a,s){var c=o&&o(e,n,i,a,s);return!0!==c&&t["_"+r]({message:e,url:n,lineNumber:i,columnNumber:a,error:s}),c},e.autoExceptionInstrumented=!0}return t}(
            {
            instrumentationKey:"INSTRUMENTATION_KEY"
            }
            );window[aiName]=aisdk,aisdk.queue&&0===aisdk.queue.length&&aisdk.trackPageView({});
        </script>';
        
        /* Insert KEY into SCRIPT */
        echo str_replace('INSTRUMENTATION_KEY', $aiKey, $rawSnippet);
    }
}
