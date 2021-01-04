/**
 * FREEMIUS DEPLOYMENT SCRIPT
 * Inspired by gulp-freemius-deploy (https://github.com/jamesckemp/gulp-freemius-deploy) by James Kemp (https://github.com/jamesckemp)
 */
const fs = require("fs");
const decompress = require('decompress');
const axios = require('axios');
const { getAuthHeaderValue } = require('./freemiusAuthenticate')

const FREEMIUS_DEVELOPER_ID = process.env.FREEMIUS_DEVELOPER_ID;
const FREEMIUS_PLUGIN_ID = process.env.FREEMIUS_PLUGIN_ID;

const DIST_DIR = '../dist';
const DIST_FREE_ZIP_FILE = 'free.zip';
const DIST_FREE_DIR = `${DIST_DIR}/free`;
const APIBASE = 'api.freemius.com';

console.log("-------------------");
console.log("FETCHING FREEMIUS BUILD");
console.log("-------------------");

(async function () {
  let versionData;
  try {
    console.log('Fetching version data from disk...');
    const rawdata = fs.readFileSync('freemiusDeployVersion.json');
    versionData = JSON.parse(rawdata);
  }

  try {
    console.log('Downloading built files...');
    await downloadFreemiusCompiledBuild(versionData);
  } catch (e) {
    console.log('Unable to fetch Freemius built version\n', e);
    process.exit(1);
  }
})();

async function downloadFreemiusCompiledBuild(versionData) {
  let authHeader;
  try {
    authHeader = await getAuthHeaderValue();
    console.log('Successfully authenticated');
  } catch (e) {
    console.error('Could not authenticate with Freemius');
    process.exit(2);
  }

  if (!versionData.id) throw new Error('Invalid version data returned');

  console.log(`Downloading v${versionData.version} from Freemius...`);

  const downloadURI = `https://${APIBASE}/v1/developers/${FREEMIUS_DEVELOPER_ID}/plugins/${FREEMIUS_PLUGIN_ID}/tags/${
    versionData.id
  }.zip?is_premium=false`;

  return axios(downloadURI, {
    method: 'get',
    headers: {
      Authorization: authHeader,
    },
    responseType: 'stream'
  }).then(response => {
    const DIST_FREE_ZIP_FILE = 'free.zip';
    const writer = fs.createWriteStream(`${DIST_DIR}/${DIST_FREE_ZIP_FILE}`)
    response.data.pipe(writer);
    return new Promise((resolve, reject) => {
      writer.on('finish', resolve)
      writer.on('error', reject)
    })
  }).then(() => {
    decompress(`${DIST_DIR}/${DIST_FREE_ZIP_FILE}`, DIST_FREE_DIR)
      .then(function() {
      console.log(`Plugin v${versionData.version} successfully downloaded`);
    });
  })
}
