/* eslint-disable no-prototype-builtins */

/**
 * Login component with login form
 * Note: Component is lazy loaded upon clicking login button
 */

import React from 'react';
import Modal from './Modal.jsx';
import UnderlinedInput from './UnderlinedInput.jsx';
import NavMenu from './NavMenu.jsx';
import { QuestionBlock, LoadingMascot } from '../lib/icons.js';

console.log('<Login> has been lazy loaded!');

const API_ENDPOINT = `${process.env.API_ENDPOINT}/login`;

const initialState = {
    isOpen: true,
    isLoading: false,
    isError: false,
    mode: 'login', // login; register
    current: 'username', // Form field to fill... username, password, email
    user: {}, // user credentials: username, email, password
    error: {},
};
const reducer = (state, action) => {
    switch (action.type) {
        case 'TOGGLE':
            return {
                ...initialState,
                isOpen: !state.isOpen,
            };
        case 'INIT':
            return {
                ...state,
                isLoading: true,
                isError: false,
            };
        case 'SUBMIT':
            return {
                ...state,
                isLoading: false,
                isError: false,
                current: action.current,
                user: {
                    ...state.user,
                    [action.inputName]: action.inputValue,
                },
            };
        case 'REGISTER':
            return {
                ...state,
                isLoading: false,
                isError: false,
                mode: 'register',
            };
        case 'LOGIN_SUCCESS':
            window.location.reload();
            return {
                ...state,
                isLoading: false,
                isError: false,
            };
        case 'LOGIN_ERROR':
            return {
                ...state,
                isLoading: false,
                isError: true,
                error: action.error ? action.error : { message: 'An error occurred.' },
            };
        default:
            throw new Error();
    }
};

export default function Login() {
    const form = React.useRef();

    const [state, dispatchState] = React.useReducer(reducer, initialState);

    const toggleOpen = (event) => {
        event.preventDefault();
        dispatchState({ type: 'TOGGLE' });
    };

    const handleSubmit = async (event) => {
        event.preventDefault();

        dispatchState({ type: 'INIT' });

        const input = form['current'][state.current]['value'];

        if (state.current === 'username') {
            const response = await fetch(`${API_ENDPOINT}/${input}`, {
                method: 'GET',
                mode: 'same-origin',
                credentials: 'same-origin',
            });
            if (response.ok) {
                const result = await response.json();
                dispatchState({
                    type: 'SUBMIT',
                    inputName: 'username',
                    inputValue: result.collection.items[0].username,
                    current: 'password',
                });
            } else {
                dispatchState({ type: 'REGISTER' });
            }
        } else if (state.current === 'password') {
            const payload = {
                ...state.user,
                password: input,
            };

            const response = await fetch(API_ENDPOINT, {
                method: 'POST',
                mode: 'same-origin',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            if (response.ok) {
                dispatchState({ type: 'LOGIN_SUCCESS' });
            } else {
                const result = await response.json();
                console.log(response, result);
                dispatchState({ type: 'LOGIN_ERROR', error: result.collection.errors[0] });
            }
        } else {
            dispatchState({ type: 'LOGIN_ERROR', error: 'An unknown error occurred.' });
        }
    };

    const LoginButton = () => (
        <a href="/login.php" title="Login" onClick={toggleOpen} className="user user-unknown">
            <QuestionBlock className="user-avatar thumbnail" />
            <span className="user-username">Login</span>
        </a>
    );

    let message;
    if (state.isError) {
        message = <div className="error">{state.error.hasOwnProperty('message') ? state.error.message : 'An error occurred'}</div>;
    } else if (state.mode === 'login') {
        if (state.current === 'username') message = "Erm... What's your name again?";
        else if (state.current === 'password') message = `That's right! I remember now! Your name is ${state.user.username}!`;
    } else if (state.mode === 'register') {
        message = 'We don\'t have anyone registered by that name. Would you like to register?';
    }

    const LoginForm = () => {
        if (state.current === 'username') {
            return <UnderlinedInput name="username" placeholder="Username or Email" padding={19} autofocus />;
        }

        return <UnderlinedInput type="password" name="password" placeholder="Password" padding={19} autofocus />;
    };

    const RegisterForm = () => 'Register form here....';

    return (
        <>
            <LoginButton />
            <Modal open={state.isOpen} close={toggleOpen}>
                <form id="loginform" ref={form} onSubmit={handleSubmit} className={state.isLoading ? 'loading' : undefined}>
                    <div id="loginform-nav">
                        <NavMenu>
                            <NavMenu.Item selected><a href="#login">New Name</a></NavMenu.Item>
                            <NavMenu.Item><a href="#blue">Blue</a></NavMenu.Item>
                            <NavMenu.Item><a href="#gary">Gary</a></NavMenu.Item>
                            <NavMenu.Item><a href="#john">John</a></NavMenu.Item>
                        </NavMenu>
                    </div>
                    <div id="loginform-rival" />
                    <div id="loginform-message">
                        {message}
                    </div>
                    <div id="loginform-input">
                        {state.mode === 'login' ? <LoginForm /> : <RegisterForm />}
                    </div>
                    <div id="loginform-submit">
                        {state.isLoading ? (
                            <>
                                <LoadingMascot className="loading" />
                                <span>Professor Oak is thinking</span>
                            </>
                        ) : (
                            <button type="submit" disabled={state.isLoading}>Submit</button>
                        )}
                    </div>
                </form>
            </Modal>
        </>
    );
}
