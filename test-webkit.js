import { webkit } from 'playwright';

(async () => {
  // Lanza el navegador WebKit. headless: false significa que veremos la ventana.
  const browser = await webkit.launch({ headless: false });
  const page = await browser.newPage();

  // Navega a tu servidor local (¡asegúrate de que esté corriendo!)
  await page.goto('http://localhost:8000'); 

  // Puedes poner una pausa para que no se cierre hasta que lo hagas manualmente
  await page.pause();

  // Cuando cierres la ventana o detengas el script, el navegador se cerrará.
  await browser.close();
})();