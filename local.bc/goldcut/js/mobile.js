// Mozilla/5.0 (iPhone; CPU iPhone OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A403 Safari/8536.25
var isPhone4inches = (window.screen.height==568);
/*
 @media (device-height: 568px) and (-webkit-min-device-pixel-ratio: 2) {
 // iPhone 5 or iPod Touch 5th generation
 <link href="startup-568h.png" rel="apple-touch-startup-image" media="(device-height: 568px)">
 <link href="startup.png" rel="apple-touch-startup-image" sizes="640x920" media="(device-height: 480px)">
 <meta name="apple-itunes-app" content="app-id=9999999">
 // https://signup.performancehorizon.com/signup/en_us/itunes
 <meta name="apple-itunes-app" content="app-id=9999999, app-argument=xxxxxx">
 <meta name="apple-itunes-app" content="app-id=9999999, app-argument=xxxxxx, affiliate-data=partnerId=99&siteID=XXXX">
 -webkit-filter: blur(5px) grayscale (.5) opacity(0.66) hue-rotate(100deg);
 background-image: -webkit-cross-fade(url("logo1.png"), url("logo2.png"), 50%);
 Chromeless webapps (using the apple-mobile-web-app-capable meta tag
 different title for the Home Screen icon <meta name="apple-mobile-web-app-title" content="My App Name">
 Safari ios Selection API through window.selection
 */