import { createTheme, responsiveFontSizes } from '@mui/material/styles';

// Define your extended color roles for light mode
const lightCustomColors = {
  primary: '#04acec',                    // Cerulean
  onPrimary: '#FFFFFF',                    // White
  primaryContainer: '#63c2f4',             // Malibu
  onPrimaryContainer: '#0C3A52',           // Deep Blue
  
  secondary: '#f97116',                    // Ecstasy (Sporty Orange)
  onSecondary: '#FFFFFF',                  // White
  secondaryContainer: '#40b4f0',           // Picton Blue
  onSecondaryContainer: '#0B2A40',         // Deep Navy
  
  tertiary: '#ff8c40',                     // Sporty Orange
  onTertiary: '#000000',                   // Black
  tertiaryContainer: '#FFD3B6',            // Soft Orange Tint
  onTertiaryContainer: '#5A1F00',          // Deep Burnt Orange
  
  error: '#B00020',                        // Material Red
  onError: '#FFFFFF',                      // White
  errorContainer: '#F9DEDC',               // Light Error
  onErrorContainer: '#410E0B',             // Dark Error
  
  background: '#F5F5F5',                   // Neutral Gray
  onBackground: '#1A1A1A',                 // Deep Black
  
  surface: '#F9FAFB',                      // Cool White
  onSurface: '#1C1C1E',                    // Dark Gray
  
  surfaceVariant: '#E1E4D5',               // Muted Sage
  onSurfaceVariant: '#44483E',             // Earthy Gray-Green
  
  outline: '#74796D',                      // Olive Gray
  outlineVariant: '#C1C9C2',               // Light Gray-Green
  
  inverseSurface: '#2E312D',               // Dark Green
  inverseOnSurface: '#E2E3DF',             // Light Gray-Green
  inversePrimary: '#1A6D23',               // Inverse Olive Green
  
  shadow: '#000000',                       // Black
  surfaceTint: '#04acec',                  // Cerulean
  scrim: '#000000AA',                      // Semi-transparent Black
  
  // Additional roles for layered surfaces
  surfaceDim: '#FCFDF6',
  surfaceBright: '#FFFFFF',
  surfaceContainerLowest: '#FFFFFF',
  surfaceContainerLow: '#F4F6E8',
  surfaceContainer: '#EFF3E2',
  surfaceContainerHigh: '#E8EDD9',
  surfaceContainerHighest: '#E2E8D1',
};

// Define your extended color roles for dark mode
const darkCustomColors = {
  primary: '#63c2f4',                    // Malibu
  onPrimary: '#0C3A52',                    // Deep Blue
  primaryContainer: '#185068',             // Darkened Malibu
  onPrimaryContainer: '#A0E2FF',           // Light Blue
  
  secondary: '#ff8c40',                    // Sporty Orange
  onSecondary: '#000000',                  // Black
  secondaryContainer: '#78350A',           // Dark Orange
  onSecondaryContainer: '#F3CEB8',         // Light Orange
  
  tertiary: '#DD7A30',                     // Warm Amber
  onTertiary: '#000000',                   // Black
  tertiaryContainer: '#5E3C52',            // Deep Mauve
  onTertiaryContainer: '#F2DDE9',          // Light Pinkish Lavender
  
  error: '#CF6679',                        // Material Red
  onError: '#000000',                      // Black
  errorContainer: '#661511',               // Dark Error
  onErrorContainer: '#E6ACA9',             // Light Red Tint
  
  background: '#1A1C19',                   // Dark Olive
  onBackground: '#E2E3DF',                 // Soft White
  
  surface: '#131412',                      // Dark Charcoal
  onSurface: '#E3E8DF',                    // Light Mint Gray
  
  surfaceVariant: '#414941',               // Deep Sage
  onSurfaceVariant: '#C1C9C2',             // Light Gray-Green
  
  outline: '#8E938D',                      // Gray Green
  outlineVariant: '#4F4539',               // Deep Taupe
  
  inverseSurface: '#E2E3DF',               // Light Gray-Green
  inverseOnSurface: '#2E312D',             // Deep Green
  inversePrimary: '#1A6D23',               // Inverse Olive Green
  
  shadow: '#000000',                       // Black
  surfaceTint: '#7FDB7C',                  // Bright Green
  scrim: '#000000AA',                      // Semi-transparent Black
  
  // Additional roles for layered surfaces
  surfaceDim: '#1A1C19',
  surfaceBright: '#2E312D',
  surfaceContainerLowest: '#141614',
  surfaceContainerLow: '#202421',
  surfaceContainer: '#262A26',
  surfaceContainerHigh: '#2E322E',
  surfaceContainerHighest: '#373B37',
};

// Create the theme. We define the base theme for light mode and add a custom property for the extended colors.
let theme = createTheme({
  palette: {
    mode: 'light', // Default mode; can be toggled dynamically
    primary: {
      main: lightCustomColors.primary,
      contrastText: lightCustomColors.onPrimary,
    },
    secondary: {
      main: lightCustomColors.secondary,
      contrastText: lightCustomColors.onSecondary,
    },
    error: {
      main: lightCustomColors.error,
    },
    background: {
      default: lightCustomColors.background,
      paper: lightCustomColors.surface,
    },
    // Add a custom property with your extended color roles
    custom: { ...lightCustomColors },
  },
  typography: {
    fontFamily: '"Roboto", sans-serif',
    h1: { fontSize: '3rem', fontWeight: 700, lineHeight: 1.2 },
    h2: { fontSize: '2.5rem', fontWeight: 700, lineHeight: 1.2 },
    h3: { fontSize: '2rem', fontWeight: 600, lineHeight: 1.3 },
    h4: { fontSize: '1.75rem', fontWeight: 600, lineHeight: 1.4 },
    h5: { fontSize: '1.5rem', fontWeight: 500, lineHeight: 1.5 },
    h6: { fontSize: '1.25rem', fontWeight: 500, lineHeight: 1.6 },
    body1: { fontSize: '1rem', fontWeight: 400, lineHeight: 1.5 },
    body2: { fontSize: '0.875rem', fontWeight: 400, lineHeight: 1.6 },
    subtitle1: { fontSize: '1rem', fontWeight: 400, lineHeight: 1.5 },
    subtitle2: { fontSize: '0.875rem', fontWeight: 400, lineHeight: 1.6 },
    caption: { fontSize: '0.75rem', fontWeight: 400, lineHeight: 1.4 },
    button: { fontSize: '0.875rem', fontWeight: 500, textTransform: 'none' },
  },
  components: {
    MuiAppBar: {
        styleOverrides: {
          root: {
            padding: '0 16px',
            display: 'flex',
            alignItems: 'center',
            // Use responsive height values (adjust as desired)
            height: {
              xs: '15vh',  // e.g., 15% of viewport height on extra-small screens (mobile)
              sm: '20vh',  // 20% on small screens
              md: '25vh',  // 25% on medium screens
              lg: '30vh',  // 30% on large screens
            },
            boxShadow: '0px 4px 6px rgba(0, 0, 0, 0.2)',
          },
        },
      },
      MuiToolbar: {
        styleOverrides: {
          root: {
             // Make the Toolbar’s minimum height follow the same responsive values
              minHeight: {
                xs: '15vh !important',
                sm: '20vh !important',
                md: '25vh !important',
                lg: '30vh !important',
              },
            // Let the Toolbar's height expand naturally within its container
            height: '100%',
          },
        },
    // Other component overrides remain as defined earlier...
      },
  },
});

// For dark mode, you would merge in darkCustomColors to palette.custom and adjust background, etc.
if (theme.palette.mode === 'dark') {
  theme = createTheme(theme, {
    palette: {
      background: {
        default: darkCustomColors.background,
        paper: darkCustomColors.surface,
      },
      custom: { ...darkCustomColors },
    },
    components: {
      MuiAppBar: {
        styleOverrides: {
          root: {
            backgroundColor: darkCustomColors.background,
            color: darkCustomColors.onBackground,
          },
        },
      },
      MuiDrawer: {
        styleOverrides: {
          root: {
            backgroundColor: darkCustomColors.background,
          },
          paper: {
            backgroundColor: darkCustomColors.background,
          },
        },
      },
    },
  });
}

theme = responsiveFontSizes(theme);

export default theme;

