import React from 'react';
import ReactDOM from 'react-dom';
import './assets/fonts/MyriadPro-Bold.otf';
import './assets/fonts/MyriadPro-Regular.otf';
import './assets/fonts/RobotoBlack.ttf';
import './assets/fonts/Roboto-Medium_1.ttf';
import './assets/fonts/PatuaOne-Regular.ttf';
import './index.css';
import App from './App';
import reportWebVitals from './reportWebVitals';
import Preloader from "./Preloader";
import responsiveModule from "./responsiveModule";
//import {setup} from "./Game";

ReactDOM.render(
  <React.StrictMode>
    <Preloader />
    <App />
  </React.StrictMode>,
  document.getElementById('root')
);
// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
reportWebVitals();
//
responsiveModule();