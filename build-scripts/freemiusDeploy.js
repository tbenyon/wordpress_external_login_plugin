/**
 * FREEMIUS DEPLOYMENT SCRIPT
 * Inspired by gulp-freemius-deploy (https://github.com/jamesckemp/gulp-freemius-deploy) by James Kemp (https://github.com/jamesckemp)
 */
const fs = require("fs");
const cryptojs = require("crypto-js");
const decompress = require('decompress');
const zipper = require('./zipper')
const axios = require('axios');
const FormData = require('form-data');

const FREEMIUS_DEVELOPER_ID = process.env.FREEMIUS_DEVELOPER_ID;
const FREEMIUS_PLUGIN_ID = process.env.FREEMIUS_PLUGIN_ID;
const FREEMIUS_PK = process.env.FREEMIUS_PK;
const FREEMIUS_SK = process.env.FREEMIUS_SK;

const PLUGIN_FILES_SRC_PATH = '../plugin-files/';
const SRC_BUILD_ARTEFACTS_DIR = './build_artefacts';
const SRC_ZIP_FILE = 'src.zip';
const SRC_ZIP_PATH = `${SRC_BUILD_ARTEFACTS_DIR}/${SRC_ZIP_FILE}`;
const DIST_DIR = '../dist';
const DIST_FREE_ZIP_FILE = 'free.zip';
const DIST_FREE_DIR = `${DIST_DIR}/free`;
const APIBASE = 'api.freemius.com';

console.log("-------------------");
console.log("FREEMIUS DEPLOYMENT");
console.log("-------------------");

console.log(`\nCreating deployment zip (${SRC_ZIP_FILE})...`);

[SRC_BUILD_ARTEFACTS_DIR, DIST_DIR].forEach((dir) => {
  if (!fs.existsSync(dir)){
    fs.mkdirSync(dir);
  }
});

(async function () {
  try {
    await zipper.zip(PLUGIN_FILES_SRC_PATH, SRC_ZIP_PATH);
  } catch(e) {
    console.log('Unable to Zip plugin\n', e);
    process.exit(1);
  }

  let tokens;
  try {
    tokens = await getFreemiusAuthTokens();
    if (!tokens.access) throw new Error('No access token returned');
    console.log('Successfully authenticated');
  } catch (e) {
    console.error('Could not authenticate with Freemius');
    process.exit(2);
  }

  const deployHeader = `FSA ${FREEMIUS_DEVELOPER_ID}:${tokens.access}`;
  const deployDate = new Date().toUTCString();

  let versionData;
  try {
    console.log('Deploying to Freemius...');
    versionData = await deployZipToFreemius(deployHeader, deployDate);
  } catch (e) {
    console.log('Unable to deploy version\n', e);
    process.exit(3);
  }

  try {
    console.log('Downloading built files...');
    await downloadFreemiusCompiledBuild(versionData, deployHeader, deployDate);
  } catch (e) {
    console.log('Unable to fetch Freemius built version\n', e);
    process.exit(4);
  }
})();

function deployZipToFreemius(deployHeader, deployDate) {
    const deployURI = `/v1/developers/${FREEMIUS_DEVELOPER_ID}/plugins/${FREEMIUS_PLUGIN_ID}/tags.json`;
    const deployBoundary = "----" + new Date().getTime().toString(16);

    const form = new FormData();
    form.append('file', fs.createReadStream(SRC_ZIP_PATH));

    const request_config = {
      headers: {
        "Content-MD5": "",
        Date: deployDate,
        Authorization: deployHeader,
        ...form.getHeaders()
      }
    };

    return axios.post(
        `https://${APIBASE}${deployURI}`,
        form,
        request_config
    )
    .then(response => response.data)
    .catch((err) => {
      console.log('Upload failed', err);
    })
}

function getFreemiusAuthTokens() {
  console.log("Authenticating...");

  const authDate = new Date().toUTCString();
  const authURI = `/v1/developers/${FREEMIUS_DEVELOPER_ID}/token.json`;
  const authHeader = `FS ${FREEMIUS_DEVELOPER_ID}:${FREEMIUS_PK}:${cryptojs.enc.Base64.stringify(
    cryptojs.enc.Utf8.parse(
      cryptojs.HmacSHA256(["GET", "", "application/json", authDate, authURI].join("\n"), FREEMIUS_SK).toString()
    )
  ).replace(/=/g, "")}`;

  return axios(`https://api.freemius.com${authURI}`, {
    method: 'get',
    headers: {
      "Content-MD5": "",
      "Content-Type": "application/json",
      Date: authDate,
      Authorization: authHeader,
    },
  })
  .then(json => {
    if (typeof json.error !== "undefined") {
      throwFetchAuthTokenError(json.error);
    }
    return json.data;
  })
  .catch(err => throwFetchAuthTokenError(err));

  function throwFetchAuthTokenError(reason) {
    throw new Error(`Failed to fetch Feemius auth token:\n${reason}`);
  }
}

function downloadFreemiusCompiledBuild(versionData, deployHeader, deployDate) {
  if (!versionData.id) throw new Error('Invalid version data returned');

  console.log(`Downloading v${versionData.version} from Freemius...`);

  const downloadURI = `https://${APIBASE}/v1/developers/${FREEMIUS_DEVELOPER_ID}/plugins/${FREEMIUS_PLUGIN_ID}/tags/${
    versionData.id
  }.zip?is_premium=false`;

  return axios(downloadURI, {
    method: 'get',
    headers: {
      Authorization: deployHeader,
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
