=== EDD Crowdfunding ===
Contributors: garubi
Tags: EDD, Easy Digital Download, Crowdfunding
Requires at least: 6.4.0
Tested up to: 6.4.3
Requires PHP: 8.0
Stable tag: trunk
License: GPL-3.0 license
License URI: https://github.com/garubi/edd-crowdfunding?tab=GPL-3.0-1-ov-file#readme

Basic plugin to host a crowdfunding campaign with Easy Digital Download.

== Description ==
Usage:
`[edd_crowdfunding target="3500" mode="Raccogli tutto" launch="01-01-2021" deadline="31-12-2021"]`
Show a table with the campaign's status

Where:
- `target` è l'obiettivo di raccolta; 
- `mode` è la modalità di raccolta, è un testo libero, scrivi quello che vuoi; 
- `launch` è la data di inizio della campagna; 
- `deadline` è la data di scadenza della campagna, una volta superata verrà scritto "campagna terminata"

Usage:
[edd_pledgers launch="14-02-2021" deadline="18-05-2021" include_email='false' format='textarea']
Show a list of edd_pledgers

Where:
- `launch` è la data di inizio della campagna; 
- `deadline` è la data di scadenza della campagna; Tutti i clienti che hannpo acquistato in questo periodo sono elencati
- include_email: 'true' o 'false'. Se 'true' elenca l'indirizzo email del cliente oltre al suo nome
- format: 'list' o 'textarea' (questo soprattutto per fare copia-incolla nei programmi di mass mailing). Se 'list' elenca tutti i clienti separati da 'separator'. Se 'textarea', mostra all'interno di una textarea i clienti uno per riga, con l'eventuale email separata da 'separator'
- separator: default ','. I separatore della lista.

== Changelog ==
1.2.0 [2024-03-25] - Add [edd_pledgers] shortcode
1.1.0 [2024-02-23] - Updated to consider all of the pledges in the date range (was capped to 30 by EDD)
1.0.0 [2021-01-02] - First stable release