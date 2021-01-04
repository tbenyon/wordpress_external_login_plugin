/**
 * FREEMIUS DEPLOYMENT SCRIPT
 * Inspired by gulp-freemius-deploy (https://github.com/jamesckemp/gulp-freemius-deploy) by James Kemp (https://github.com/jamesckemp)
 */
const fs = require("fs");
const zipper = require('./zipper')
const axios = require('axios');
const FormData = require('form-data');
const { getAuthHeaderValue } = require('./freemiusAuthenticate')

const FREEMIUS_DEVELOPER_ID = process.env.FREEMIUS_DEVELOPER_ID;
const FREEMIUS_PLUGIN_ID = process.env.FREEMIUS_PLUGIN_ID;

const PLUGIN_FILES_SRC_PATH = '../plugin-files/';
const SRC_BUILD_ARTEFACTS_DIR = './build_artefacts';
const SRC_ZIP_FILE = 'src.zip';
const SRC_ZIP_PATH = `${SRC_BUILD_ARTEFACTS_DIR}/${SRC_ZIP_FILE}`;
const APIBASE = 'api.freemius.com';

console.log("-------------------");
console.log("FREEMIUS DEPLOYMENT");
console.log("-------------------");


if (!fs.existsSync(SRC_BUILD_ARTEFACTS_DIR)){
  fs.mkdirSync(SRC_BUILD_ARTEFACTS_DIR);
}

(async function () {
  try {
    console.log('Zipping source files...');
    await zipper.zip(PLUGIN_FILES_SRC_PATH, SRC_ZIP_PATH);
  } catch(e) {
    console.log('Unable to Zip plugin\n', e);
    process.exit(1);
  }

  let versionData;
  try {
    console.log('Deploying to Freemius...');
    versionData = await deployZipToFreemius();
  } catch (e) {
    console.log('Unable to deploy version\n', e);
    process.exit(2);
  }

  try {
    console.log('Recording Freemius Version Data...');
    let data = JSON.stringify(versionData);
    fs.writeFileSync('freemiusDeployVersion.json', data);
  } catch (e) {
    console.log('Unable to write version data to file\n', e);
    process.exit(3);
  }
})();

async function deployZipToFreemius() {
    let authHeader;
    try {
      authHeader = await getAuthHeaderValue();
      console.log('Successfully authenticated');
    } catch (e) {
      console.error('Could not authenticate with Freemius');
      process.exit(4);
    }

    const deployURI = `/v1/developers/${FREEMIUS_DEVELOPER_ID}/plugins/${FREEMIUS_PLUGIN_ID}/tags.json`;

    const form = new FormData();
    form.append('file', fs.createReadStream(SRC_ZIP_PATH));

    const request_config = {
      headers: {
        "Content-MD5": "",
        Date: new Date().toUTCString(),
        Authorization: authHeader,
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
