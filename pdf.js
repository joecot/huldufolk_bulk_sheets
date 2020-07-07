const htmlPdf = require('html-pdf-chrome');
 
const options = {
  port: 9222, // port Chrome is listening on
  headerTemplate: ``,
    footerTemplate: '',
};
file = process.argv[2];
url = process.argv[3];
htmlPdf.create(url, options).then((pdf) => pdf.toFile(file));
