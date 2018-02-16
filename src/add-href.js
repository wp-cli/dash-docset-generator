(() => {
  if (document.readyState === 'complete' || document.readyState !== 'loading') {
    main()
  } else {
    document.addEventListener('DOMContentLoaded', main)
  }

  function main () {
    const anchors = document.querySelectorAll('[data-relative-href]')
    anchors.forEach(addHref)
  }

  /**
   * @param {Element} element
   */
  function addHref (element) {
    const currentDir = window
      .location
      .href
      .match(/^((.*?)\/WP-CLI.docset\/Contents\/Resources\/Documents).*?$/)[1]
    const relativePath = element.getAttribute('data-relative-href')
    element.setAttribute('href', currentDir + relativePath)
  }
})()
