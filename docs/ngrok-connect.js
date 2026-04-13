const fs = require("fs");
const path = require("path");
const ngrok = require(process.env.APPDATA + "\\npm\\node_modules\\ngrok");

const outputPath = path.join(
  "C:",
  "Users",
  "modis",
  "Desktop",
  "take_home_assignment_Modisa",
  "ngrok-url.txt",
);

async function main() {
  const url = await ngrok.connect({ addr: 8090 });
  fs.writeFileSync(outputPath, url, "utf8");
  console.log(url);
  setInterval(() => {}, 1 << 30);
}

main().catch((error) => {
  fs.writeFileSync(outputPath, "ERROR: " + error.message, "utf8");
  console.error(error);
  process.exit(1);
});
