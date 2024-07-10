# Discord Stream Notifictions
This script when run will get a list of currently live streams from Twitch relating to a specific game and post the top result to a Discord Webhook.

The following environment variables need to be added into `local.env`:
* `WEBHOOK_URL` - The webhook URL you get from Discord on your server under Server Settings>Apps>Integrations
* `TWITCH_CLIENT_ID` - Generated from https://dev.twitch.tv/console/apps/create (*ensure you choose "Confidential" when setting it up*)
* `TWITCH_CLIENT_SECRET` Also generated from https://dev.twitch.tv/console/apps/create

## Running locally
This repo is set up to run under Docker, just clone the repo and run `docker-compose up`, you can then browse to http://localhost:8080 to trigger the script.