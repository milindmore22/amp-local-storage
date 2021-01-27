/**
 * Test Local Storage.
 */
const testLocalStorage = () => {
    const currentVisitedURL = document.getElementById( 'page-url' ).textContent;
    let allVisitedURLs = new Array();

    // Get Existing Data if avilable else push current site URL.
    if( localStorage.getItem( 'visited-urls' ) ) {
      // Get From Local storage and covert to array.
      allVisitedURLs = JSON.parse( localStorage.getItem( 'visited-urls' ) );
      // Push URL in array.
      allVisitedURLs.push( currentVisitedURL.trim() );
    } else {
      // Push First Page.
      allVisitedURLs.push( currentVisitedURL.trim() );
    }
    // Store in Local Storage.
    localStorage.setItem( 'visited-urls',  JSON.stringify( allVisitedURLs ) );

}

testLocalStorage();
