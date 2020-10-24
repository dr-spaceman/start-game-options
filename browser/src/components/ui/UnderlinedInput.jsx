import React from 'react';

const typography = {
    fontFamily: 'Press Start',
};

export default function UnderlinedInput({ value = '', type = 'text', padding = 10, autofocus, ...props }) {
    const [state, setState] = React.useState(value);
    const handleChange = (event) => {
        setState(event.target.value);
    };
    const placeholderPad = padding - state.length;
    const placeholder = '_'.repeat(placeholderPad > 0 ? placeholderPad : 0);

    if (autofocus) {
        props.ref = (input) => input && input.focus();
    }

    return (
        <div className="underlinedinput" style={{ position: 'relative' }}>
            <div className="underline" style={{ position: 'absolute', left: 0, top: '.3em', zIndex: 0, ...typography }}>
                <span style={{ opacity: 0 }}>{state}</span>
                <span>{placeholder}</span>
            </div>
            <input type={type} value={state} onChange={handleChange} style={{ position: 'relative', zIndex: 1, ...typography }} {...props} />
        </div>
    );
}
