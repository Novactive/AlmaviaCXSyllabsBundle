import React, {Component} from 'react';
import PropTypes from 'prop-types';
import {loadSuggestions} from '../module/syllabs.suggester.service';
import SyllabsSuggestionComponent, {Suggestion} from '../module/syllabs.suggestion.component';
import {TAB_CREATE} from '../../../../../../../../../ezsystems/ezplatform-admin-ui-modules/src/modules/universal-discovery/universal.discovery.module';

export default class SyllabsSuggesterModule extends Component {

  constructor(props, context) {
    super(props, context);

    this.state = {
      suggestions: [],
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
  }

  /**
   * @param {Object} document
   */
  updateSuggestions(document) {
    const suggestions = [];
    for (const annotationType in this.props.annotationTypeConfigs) {
      const annotationTypeConfig = this.props.annotationTypeConfigs[annotationType];
      for (const suggestion of document[annotationType]) {
        suggestions.push(
            new Suggestion({
              type: annotationType,
              text: suggestion.text,
              parentTagId: annotationTypeConfig.parentTagId,
            }),
        );
      }
    }

    this.setState({
      suggestions: suggestions,
    });
  }

  /**
   * @param {Suggestion} clickedSuggestion
   * @param {MouseEvent} event
   */
  onSuggestionClick(clickedSuggestion, event) {
    const {suggestions} = this.state
    for (const suggestion of suggestions) {
      if(suggestion === clickedSuggestion) {
        suggestion.selected = !clickedSuggestion.selected
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
    return <div className="form-check" key={suggestion.text}>
      <SyllabsSuggestionComponent suggestion={suggestion} onClick={this.onSuggestionClick.bind(this, suggestion)}/>
    </div>;
  }

  /**
   * @returns {Array<Suggestion>}
   */
  getSelectedSuggestions() {
    const selectedSuggestions = []
    for (const suggestion of this.state.suggestions) {
      if(suggestion.selected) {
        selectedSuggestions.push(suggestion)
      }
    }
    return selectedSuggestions
  }

  handleConfirm() {
    this.props.onConfirm(this.getSelectedSuggestions());
  }

  renderConfirmBtn() {
    const { suggestions } = this.state;
    const attrs = {
      className: 'm-ud__action m-ud__action--confirm',
      type: 'button',
      onClick: this.handleConfirm.bind(this),
    };
    const confirmBtnLabel = Translator.trans(/*@Desc("Confirm")*/ 'confirm.label', {}, 'universal_discovery_widget');
    if(this.getSelectedSuggestions().length === 0) {
      return null
    }


    return <button {...attrs}>{confirmBtnLabel}</button>;
  }

  render() {
    const componentClassName = 'm-ud syllabs-suggester-module';
    let containerClassName = `${componentClassName}`;
    const cancelBtnLabel = Translator.trans(/*@Desc("Cancel")*/ 'cancel.label',
        {}, 'universal_discovery_widget');
    if(this.state.suggestions.length > 0) {
      containerClassName += ' loaded'
    }
    return (
        <div className="m-ud__wrapper">
          <div className={containerClassName}>
            <h1 className="m-ud__title">{this.props.title}</h1>
            <div className="m-ud__content-wrapper">
              <div className="m-ud__content">
                <div className="loader">
                  <svg className="ez-icon ez-spin">
                    <use xlinkHref="/bundles/ezplatformadminui/img/ez-icons.svg#spinner"></use>
                  </svg>
                </div>
                {this.state.suggestions.map(this.renderSuggestion.bind(this))}
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
};

SyllabsSuggesterModule.defaultProps = {
  title: 'Suggestions',
};
