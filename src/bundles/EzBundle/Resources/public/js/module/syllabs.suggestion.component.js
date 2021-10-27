import React, {Component} from 'react';
import SyllabsSuggesterModule from './syllabs.suggester.module';

export class Suggestion {
  constructor({type, text, selected, parentTagId, subtype, score}) {
    this.type = type
    this.text = text
    this.selected = selected || false
    this.parentTagId = parentTagId
    this.subtype = subtype
    this.score = score
  }
}

export default class SyllabsSuggestionComponent extends Component {

  constructor(props, context) {
    super(props, context);
  }

  render() {
    return <label className="form-check-label">
      <input type="checkbox"
             name={`suggestion[${this.props.suggestion.type}]`}
             value={this.props.suggestion.text}
             checked={this.props.suggestion.selected ? 'checked' : ''}
             onClick={this.props.onClick}
             className="form-check-input"/>
      {this.props.suggestion.text} (score: {this.props.suggestion.score})
    </label>
  }
}

SyllabsSuggestionComponent.propTypes = {
  suggestion: PropTypes.instanceOf(Suggestion).isRequired,
  onClick: PropTypes.func.isRequired
}
