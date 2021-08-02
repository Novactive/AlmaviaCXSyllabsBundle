(function(global, doc, eZ, React, ReactDOM, Translator) {
  console.log(global.eZ)
  const btns = doc.querySelectorAll('.syllabs-suggester');

  const suggesterContainer = doc.getElementById('react-udw');
  const token = doc.querySelector('meta[name="CSRF-Token"]').content;
  const siteaccess = doc.querySelector('meta[name="SiteAccess"]').content;

  const closeSuggester = () => ReactDOM.unmountComponentAtNode(suggesterContainer);
  const onConfirm = (tags) => {

  };
  const onCancel = () => closeSuggester();
  const openSuggester = (event) => {
    event.preventDefault();
    event.stopPropagation();

    const config = JSON.parse(event.currentTarget.dataset.suggesterConfig);
    const title = Translator.trans(/*@Desc("Select location")*/ 'add_location.title', {}, 'universal_discovery_widget');

    ReactDOM.render(
        React.createElement(
            eZ.modules.SyllabsSuggester,
            Object.assign(
                {
                  onConfirm,
                  onCancel,
                  title,
                  restInfo: {token, siteaccess},
                },
                config
            )
        ),
        suggesterContainer
    );
  };

  btns.forEach((btn) => btn.addEventListener('click', openSuggester, false));

})(window, document, window.eZ, window.React, window.ReactDOM, window.Translator);
