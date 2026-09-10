Font locali richiesti da assets/css/fonts.css
==============================================

Questo plugin non scarica più i font da Google Fonts (fonts.googleapis.com /
fonts.gstatic.com). I font vengono dichiarati con @font-face locali in
assets/css/fonts.css e devono essere forniti come file WOFF2 legittimamente
licenziati, copiati manualmente in questa cartella (assets/fonts/).

Nessun file viene incluso automaticamente nel repository: finché i file
sottostanti non vengono aggiunti, il CSS ricade sui font di fallback di
sistema dichiarati nello stesso file (font-display: optional), senza errori
404 bloccanti (i preload vengono stampati SOLO se il file esiste già su
disco, vedi GAPS_Assets::output_font_preloads()).

File attesi (nome esatto, minuscolo):

  fredoka-600.woff2        Fredoka SemiBold (600) — usato in tutti i titoli
                             (h1/h2/h3) e nel box "Il sistema".
  fredoka-700.woff2        Fredoka Bold (700) — usato nei pulsanti CTA, nel
                             prezzo, nei badge e in vari elementi enfatizzati.
  karla-400.woff2           Karla Regular (400) — testo body predefinito.
  karla-600.woff2           Karla SemiBold (600) — barra in alto, etichette
                             dei campi del popup di preordine.
  karla-700.woff2           Karla Bold (700) — eyebrow, badge, elementi in
                             grassetto nel corpo del testo.
  lora-400.woff2             Lora Regular (400) — paragrafi della sezione
                             "Storia vera" e altri blocchi in corsivo/serif.
  lora-400-italic.woff2      Lora Italic (400) — citazioni ed enfasi corsive.
  lora-700.woff2             Lora Bold (700) — usato solo da
                             ".gaps-story-emphasis" (sezione Storia).

Pesi NON necessari (non richiederli): Fredoka 500 e Karla 500 sono dichiarati
nell'URL Google Fonts originale ma non risultano usati in nessuna regola CSS
del plugin — non sono stati inclusi tra i file richiesti qui sopra.

Preload: solo fredoka-600, fredoka-700, karla-400, karla-600 e karla-700
vengono precaricati in <head> (sono gli unici usati sopra la piega, nella
barra in alto e nella Hero). I file Lora non vengono mai precaricati: sono
usati solo più in basso nella pagina.
