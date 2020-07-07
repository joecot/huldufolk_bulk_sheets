
# Bulk Huldufolk Sheet Editing

This is a quick set of scripts for mass generating Huldufolk character sheets. This uses a google spreadsheet (make a copy of the [sample here](https://docs.google.com/spreadsheets/d/1wAOwIZC3uUGFOvvAN7VFBCT_k1VgcPlcG-Nd0HbqU00/edit#gid=232062656)) to allow centralized management of all your pre-made Huldufolk characters, backgrounds, and connections. Once ready, you'll be able to run sheet.php to generate separate PDFs of characters, backgrounds, and all their power descriptions.

## Setup
* Create a [Google API Project](https://developers.google.com/drive/activity/v1/guides/project). Your project will need read access to the Google Drive API, and read/write to Google Sheets API. Once you've downloaded a credentials json file, set the filename path at `$auth_config_path` at the top of sheet.php
* Install [Composer](https://getcomposer.org/), then run `composer install` to install the vendor dependencies.
* You'll need node and npm. Once you have both, you'll need pm2 installed to handle chrome headless (`npm install -g pm2`).
* Make a copy of the [sample sheet](https://docs.google.com/spreadsheets/d/1wAOwIZC3uUGFOvvAN7VFBCT_k1VgcPlcG-Nd0HbqU00/edit#gid=232062656), and edit it to your liking, by yourself or with a group of helpers! Update `$spreadsheetId` at the top of sheet.php to the ID of your google spreadsheet.
* create a directory for the destination PDFs, and set the file path at `$pdfdir` in sheet.php
* Edit the `background.php` template as appropriate for the game you're running.

## Running
* run `startchrome.sh`, which will start chrome headless in pm2
* run `php sheet.php`
  * On your first run you'll need to confirm permissions for your Google API Project to access your Google Drive/Sheets. Go to the link provided and paste in the provided confirmation code after you grant access.
  * sheet.php will go through the character sheets in your spreadsheet and generate the character sheet PDFs
  * It will then use your background.php template to character background PDFs for each character
  * It will then take the powers in those character sheets and call the [Huldufolk Powers generator](https://thehuldufolk.com/powers)  to generate PDFs of all the powers your characters have. The URL to a page with their powers will be printed on the character sheet, and also listed on your spreadsheet.
* Once you have your PDFs generated, [in Linux it's quite easy to print them all at once or in batches](https://makandracards.com/makandra/24202-linux-how-to-print-pdf-files-from-the-command-line). You'll see the PDF filenames are organized by character group code, character code, then PDF type. If you want to have players just view the powers from their phone instead of an appendix of pages to their sheet, skip printing the powers PDFs!
