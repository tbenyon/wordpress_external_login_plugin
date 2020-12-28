const fs = require('fs');
const archiver = require('archiver');

const zip = async (readPath, writePath) => {
  return new Promise((resolve, reject) => {
    const output = fs.createWriteStream(writePath);
    const archive = archiver('zip', {
      zlib: {level: 9} // Sets the compression level.
    });

    output.on('close', function () {
      resolve(archive.pointer()) // Resolve the number of bytes saved
    });

    archive.on('warning', function (err) {
        reject(err);
    });

    archive.on('error', function (err) {
      reject(err);
    });

    archive.pipe(output);

    archive.directory(readPath, false);

    archive.finalize();
  })
}

exports.zip = zip
