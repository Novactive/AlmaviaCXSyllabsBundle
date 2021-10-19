import React, {Component} from 'react';
import PropTypes from 'prop-types';
import {
  loadSuggestions,
  createSuggestions,
} from '../module/syllabs.suggester.service';
import SyllabsSuggestionComponent, {Suggestion} from '../module/syllabs.suggestion.component';
import {TAB_CREATE} from '../../../../../../../../../ezsystems/ezplatform-admin-ui-modules/src/modules/universal-discovery/universal.discovery.module';

export default class SyllabsSuggesterModule extends Component {

  constructor(props, context) {
    super(props, context);

    this._refMainContainer;
    this._refContentContainer;

    this.state = {
      suggestions: [],
      loading: true,
      maxHeight: 500
    };
  }

  componentDidMount() {
    loadSuggestions(
        {body: this.props.sourceValues},
        (response) => {
          const document = response[0];
          this.updateSuggestions(document);
        },
    );
    window.addEventListener('resize', this.updateMaxHeightState, false);

    this.setState(() => ({
      maxHeight: this._refContentContainer.clientHeight,
      mainContainerRestHeight: this._refMainContainer.clientHeight - this._refContentContainer.clientHeight,
    }));
  }

  /**
   * @param {Object} document
   */
  updateSuggestions(document) {
    const suggestions = [];
    for (const annotationType in this.props.annotationTypeConfigs) {
      const annotationTypeConfig = this.props.annotationTypeConfigs[annotationType];
      for (const suggestion of document[annotationType]) {
        if (annotationTypeConfig.subtype == '' || suggestion.type == annotationTypeConfig.subtype) {
          suggestions.push(
            new Suggestion({
              type: annotationType,
              text: suggestion.text,
              parentTagId: annotationTypeConfig.parentTagId,
              subtype: suggestion.type
            }),
          );
        }
      }
    }

    this.setState({
      suggestions: suggestions,
      loading: false
    });
  }

  /**
   * @param {Suggestion} clickedSuggestion
   * @param {MouseEvent} event
   */
  onSuggestionClick(clickedSuggestion, event) {
    const {suggestions} = this.state;
    for (const suggestion of suggestions) {
      if (suggestion === clickedSuggestion) {
        suggestion.selected = !clickedSuggestion.selected;
      }
    }
    this.setState({
      suggestions: suggestions,
    });
  }

  /**
   * @param {Suggestion} suggestion
   * @returns {JSX.Element}
   */
  renderSuggestion(suggestion) {
    return <dd className="form-check" key={suggestion.text}>
      <SyllabsSuggestionComponent suggestion={suggestion}
                                  onClick={this.onSuggestionClick.bind(this,
                                      suggestion)}/>
    </dd>;
  }

  renderSuggestionType(type, suggestions) {
    suggestions.sort(function (a, b) {
      return a.text.localeCompare(b.text);
    })
    return <dl key={type}>
      <dt className={'mb-2'}>{Translator.trans('suggestion.type.'+type+'.name', {}, 'syllabs')}</dt>
      {suggestions.map(this.renderSuggestion.bind(this))}
    </dl>
  }

  /**
   * @returns {Array<Suggestion>}
   */
  getSelectedSuggestions() {
    const selectedSuggestions = [];
    for (const suggestion of this.state.suggestions) {
      if (suggestion.selected) {
        selectedSuggestions.push(suggestion);
      }
    }
    return selectedSuggestions;
  }

  handleConfirm() {
    createSuggestions(
        {
          body: {
            languageCode: this.props.languageCode,
            suggestions: this.getSelectedSuggestions(),
          },
        },
        (response) => {
          this.props.onConfirm(response);
        },
    );
  }

  renderConfirmBtn() {
    const {suggestions} = this.state;
    const attrs = {
      className: 'm-ud__action m-ud__action--confirm',
      type: 'button',
      onClick: this.handleConfirm.bind(this),
    };
    const confirmBtnLabel = Translator.trans(/*@Desc("Confirm")*/
        'confirm.label', {}, 'universal_discovery_widget');
    if (this.getSelectedSuggestions().length === 0) {
      return null;
    }

    return <button {...attrs}>{confirmBtnLabel}</button>;
  }

  renderSuggestionsByType() {
    const types = new Map()
    if(this.state.loading) {
      return
    }
    if(this.state.suggestions.length === 0 ) {
      const noItemsMessage = Translator.trans('no_results', {}, 'syllabs')
      return <p>{noItemsMessage}</p>
    }
    for(const suggestion of this.state.suggestions) {
      const suggestionType = suggestion.type;
      if (typeof suggestion.subtype !== 'undefined') {
        suggestionType = suggestion.type +"."+suggestion.subtype.toLowerCase()
      }

      const typeSuggestions = types.get(suggestionType) || []

      typeSuggestions.push(suggestion)
      types.set(suggestionType, typeSuggestions)
    }

    const render = []
    for (const [type, suggestions] of types) {
      render.push(this.renderSuggestionType(type, suggestions))
    }

    return render
  }

  setMainContainerRef(ref) {
    this._refMainContainer = ref;
  }

  setContentContainerRef(ref) {
    this._refContentContainer = ref;
  }

  /**
   * Updates the maxHeight state
   *
   * @method updateMaxHeightState
   * @memberof UniversalDiscoveryModule
   */
  updateMaxHeightState() {
    this.setState(() => ({
      maxHeight: this._refMainContainer.clientHeight - this.state.mainContainerRestHeight,
    }));
  }

  render() {
    const componentClassName = 'm-ud syllabs-suggester-module';
    let containerClassName = `${componentClassName}`;
    const cancelBtnLabel = Translator.trans(/*@Desc("Cancel")*/ 'cancel.label',
        {}, 'universal_discovery_widget');
    if (!this.state.loading) {
      containerClassName += ' loaded';
    }
    return (
        <div className="m-ud__wrapper">
          <div className={containerClassName} ref={this.setMainContainerRef.bind(this)}>
            <h1 className="m-ud__title">{this.props.title}</h1>
            <div className="m-ud__content-wrapper">
              <div className="m-ud__content" ref={this.setContentContainerRef.bind(this)}>
                <div className="m-ud__panels">
                  <div className="loader">
                    <svg className="ez-icon ez-spin">
                      <use
                          xlinkHref="/bundles/ezplatformadminui/img/ez-icons.svg#spinner"></use>
                    </svg>
                  </div>
                  <div className="suggestions-panel" style={{ maxHeight: `${this.state.maxHeight - 96}px` }}>
                    {this.renderSuggestionsByType()}
                  </div>
                </div>
              </div>
              <div className="m-ud__actions">
                <div className="m-ud__btns">
                  <button type="button"
                          className="m-ud__action m-ud__action--cancel"
                          onClick={this.props.onCancel}>
                    {cancelBtnLabel}
                  </button>
                  {this.renderConfirmBtn()}
                </div>
              </div>
            </div>
          </div>
        </div>
    );
  }
}



eZ.addConfig('modules.SyllabsSuggester', SyllabsSuggesterModule);

SyllabsSuggesterModule.propTypes = {
  title: PropTypes.string,
  onConfirm: PropTypes.func.isRequired,
  onCancel: PropTypes.func.isRequired,
  restInfo: PropTypes.shape({
    token: PropTypes.string.isRequired,
    siteaccess: PropTypes.string.isRequired,
  }).isRequired,
  sourceValues: PropTypes.object.isRequired,
  annotationTypeConfigs: PropTypes.object.isRequired,
  languageCode: PropTypes.string.isRequired,
};

SyllabsSuggesterModule.defaultProps = {
  title: 'Suggestions',
};
