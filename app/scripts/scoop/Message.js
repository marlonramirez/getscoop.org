import { Component } from 'scalar';

export class Message extends Component {
  constructor() {
    super('#msg');
  }

  listen() {
    return {
      '.close': {
        click: () => this.type = 'not'
      }
    };
  }

  showError(msg) {
    this.msg = msg;
    this.type = 'error';
  }

  showInfo(msg) {
    this.msg = msg;
    this.type = 'info';
  }

  showSuccess(msg) {
    this.msg = msg;
    this.type = 'success';
  }

  showWarning(msg) {
    this.msg = msg;
    this.type = 'warning';
  }
}
