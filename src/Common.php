<?php

namespace ApplicationInsights\WordPress;

/**
 * Common code shared thru the application.
 */
class Common
{
    public static function getDefaultUntrackable404Files() {
        return array(
            '/sitemap.xml',
            '/favicon.ico',
            '/robots.txt',
            '/apple-touch-icon.png',
            '/apple-touch-icon-precomposed.png',
            '/apple-touch-icon-76x76.png',
            '/apple-touch-icon-76x76-precomposed.png',
            '/apple-touch-icon-120x120.png',
            '/apple-touch-icon-120x120-precomposed.png',
            '/apple-touch-icon-152x152.png',
            '/apple-touch-icon-152x152-precomposed.png',
            '/browserconfig.xml',
            '/crossdomain.xml',
            '/labels.rdf',
            '/trafficbasedsspsitemap.xml'
        );        
    }
}