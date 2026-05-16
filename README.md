# PWA Enhancer

Transform your phpBB board into a fully functional [Progressive Web App (PWA)](https://web.dev/learn/pwa/progressive-web-apps) with smart mobile device detection, allowing your forum to behave like a native application on smartphones. Additionally, it integrates the highly reliable [Mobile-Detect](https://github.com/serbanghita/Mobile-Detect) library, enabling you to force a specific style for your mobile users, entirely separate from your desktop theme. I recommend installing the [MobilePro](https://www.phpbb.com/community/viewtopic.php?t=2669155) style for this function.

## Requirements

- phpBB 3.3.0 or higher
- PHP 8.0.0 or higher
- An active SSL/HTTPS certificate on your server (Required by browsers for PWA and Service Workers)


## Features

- Instant PWA conversion: Dynamically generates the `manifest.webmanifest` using your board's name, description, and custom theme colors.
- Installable to Home Screen: Prompts and allows users to "Install" your forum to their device's home screen, functioning like a standalone app.
- Smart Mobile Style Forcing: Uses the renowned Mobile-Detect library to identify mobile devices (excluding tablets if preferred) and intelligently applies a specific mobile template (e.g., MobilePro) of your choosing.
- PWA Persisted Style: Differentiates between normal mobile browsing and accesses directly via the installed App icon (PWA Mode) and locks their session to the correct style.
- Service Worker Integration: Includes a basic cache-first `sw.js` for statically caching assets, making your board load significantly faster on returning visits and adding pseudo-offline stability.
- Advanced ACP Configuration: A complete control panel to enable/disable the extension, customize background and theme colors via a color picker, define the forced mobile style, and set the app icons.

## Extension Demo
 - [https://vinny.quest/phpbb/index.php](https://vinny.quest/phpbb/index.php) (open on a mobile device)


## Screenshots

<img src="https://i.imgur.com/R6yygzC.png" width="400">
<img src="https://i.imgur.com/eIR9z0B.png" width="400">
<img src="https://i.imgur.com/ofeglWq.png" width="400">
<img src="https://i.imgur.com/Yq3lQuj.png" width="400">


<img src="https://i.imgur.com/RJBI8pX.png" width="800">


<img src="https://i.imgur.com/2KntQh5.png" width="800">


## Support This Project
[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/vinny1)

## License

[GNU General Public License v2](license.txt)