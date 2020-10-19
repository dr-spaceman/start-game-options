import React from 'react';

const themes = {
    light: {
        foreground: "#000000",
        background: "#eeeeee",
        name: 'light',
    },
    dark: {
        foreground: "#ffffff",
        background: "#222222",
        name: 'dark',
    }
};
// Create a context for the current theme (with "light" as the default).
const ThemeContext = React.createContext(themes.light);
function Test() {
    // Use a Provider to pass the current theme to the tree below.
    // Any component can read it, no matter how deep it is.
    // In this example, we're passing "dark" as the current value.
    return (
        <ThemeContext.Provider value={themes.dark}>
            <Toolbar />
        </ThemeContext.Provider>
    );
}
// A component in the middle doesn't have to
// pass the theme down explicitly anymore.
function Toolbar() {
    return (
        <div>
            <ThemedButton />
        </div>
    );
}
function ThemedButton() {
    // Assign a contextType to read the current theme context.
    // React will find the closest theme Provider above and use its value.
    // In this example, the current theme is "dark".
    const theme = React.useContext(ThemeContext);
    return <button style={{ background: theme.background, color: theme.foreground }}>{theme.name}</button>
}

export default Test;
