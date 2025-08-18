declare global {
  interface Window {
    toast?: {
      default: (message: string, options?: any) => string
      success: (message: string, options?: any) => string
      error: (message: string, options?: any) => string
      warning: (message: string, options?: any) => string
    }
  }
}

export {}
