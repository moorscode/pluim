# Pluim
Slackbot to give compliments to others with custom GIFS

![Pluim](https://github.com/wgroenewold/pluim/raw/main/pluim_icon.jpg)


## Setup
- Clone repo
- ```composer install/update```
- Create Slack App and get App ID 
- Activate [incoming webhooks](https://api.slack.com/apps/YOURAPPID/interactive-messages)
- Activate [slash command /pruim](https://api.slack.com/apps/YOURAPPID/slash-commands)
- Create dialog with [Block Kit Builder](https://api.slack.com/tools/block-kit-builder) and dump in ```dialog.json```
- Set scope with [OAuth & Permissions](https://api.slack.com/apps/AKRSMC3FY/oauth)

## Note
Images like the filename of id_filename.gif like 14_drinking_champagne.gif

