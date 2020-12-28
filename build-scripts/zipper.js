const zip = (readPath, writePath) => {
  console.log('starting zip')
// require modules
  const fs = require('fs');
  const archiver = require('archiver');

  return new Promise((resolve, reject) => {
    // create a file to stream archive data to.
    const output = fs.createWriteStream(__dirname + writePath);
    const archive = archiver('zip', {
      zlib: {level: 9} // Sets the compression level.
    });

    // on complete
    output.on('close', function () {
      console.log('fin')
      resolve(archive.pointer()) // Resolves with the total bytes
    });

    archive.on('warning', function (err) {
      console.log('warn')
      reject(err);
      throw err;
    });

    archive.on('error', function (err) {
      console.log('err', err)
      reject(err);
      throw err;
    });

    // pipe archive data to the file
    archive.pipe(output);

    // append files from
    archive.directory(readPath, false);

// finalize the archive (ie we are done appending files but streams have to finish yet)
// 'close', 'end' or 'finish' may be fired right after calling this method so register to them beforehand
    archive.finalize();
  })

}

exports.zip = zip
